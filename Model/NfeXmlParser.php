<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Xml\Parser;

class NfeXmlParser
{
    /**
     * @var Parser
     */
    private $xmlParser;

    /**
     * @param Parser $xmlParser
     */
    public function __construct(
        Parser $xmlParser
    ) {
        $this->xmlParser = $xmlParser;
    }

    /**
     * Parse NFe XML content and extract invoice data
     *
     * @param string $xmlContent
     * @return array
     * @throws LocalizedException
     */
    public function parseNfeXml($xmlContent)
    {
        try {
            $this->xmlParser->loadXML($xmlContent);
            $xmlArray = $this->xmlParser->xmlToArray();
            
            // Handle different NFe XML structures
            $nfe = null;
            $infNfe = null;
            
            // First check if we have nfeProc structure
            if (isset($xmlArray['nfeProc'])) {
                $nfeProc = $xmlArray['nfeProc'];
                // Check if nfeProc has _value (namespace)
                if (isset($nfeProc['_value']) && isset($nfeProc['_value']['NFe'])) {
                    $nfe = $nfeProc['_value']['NFe'];
                } elseif (isset($nfeProc['NFe'])) {
                    // NFe might be with namespace attributes
                    if (isset($nfeProc['NFe']['_value'])) {
                        $nfe = $nfeProc['NFe']['_value'];
                    } else {
                        $nfe = $nfeProc['NFe'];
                    }
                }
            } elseif (isset($xmlArray['NFe'])) {
                // Direct NFe structure
                if (isset($xmlArray['NFe']['_value'])) {
                    $nfe = $xmlArray['NFe']['_value'];
                } else {
                    $nfe = $xmlArray['NFe'];
                }
            }
            
            if (!$nfe) {
                throw new LocalizedException(__('Could not find NFe element in XML'));
            }
            
            // Extract infNFe
            if (isset($nfe['infNFe']['_value'])) {
                $infNfe = $nfe['infNFe']['_value'];
            } elseif (isset($nfe['infNFe'])) {
                $infNfe = $nfe['infNFe'];
            }
            
            if (!$infNfe) {
                throw new LocalizedException(__('Could not find infNFe element in XML'));
            }
            
            return $this->extractInvoiceData($infNfe, $nfe);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Error parsing NFe XML: %1', $e->getMessage()));
        }
    }

    /**
     * Extract invoice data from parsed NFe XML
     *
     * @param array $infNfe
     * @param array $nfe
     * @return array
     */
    private function extractInvoiceData($infNfe, $nfe = null)
    {
        $ide = $infNfe['ide'];
        $dest = $infNfe['dest'];
        $total = $infNfe['total']['ICMSTot'];
        
        // Try to get the key from the infNFe attributes
        $key = '';
        if ($nfe && isset($nfe['infNFe']['_attribute']['Id'])) {
            $key = $nfe['infNFe']['_attribute']['Id'];
        } elseif (isset($infNfe['_attribute']['Id'])) {
            $key = $infNfe['_attribute']['Id'];
        }
        
        $invoiceData = [
            'series' => $ide['serie'] ?? '',
            'number' => $ide['nNF'] ?? '',
            'key' => $key,
            'date' => $ide['dhEmi'] ?? $ide['dEmi'] ?? '',
            'total_value' => $total['vNF'] ?? '0.00',
            'products_value' => $total['vProd'] ?? '0.00',
            'cfop' => $this->extractCfop($infNfe),
            'order_increment_id' => $this->extractOrderNumber($infNfe)
        ];
        
        // Clean the key - remove NFe prefix if present
        if (strpos($invoiceData['key'], 'NFe') === 0) {
            $invoiceData['key'] = substr($invoiceData['key'], 3);
        }
        
        // Format date to MySQL format if needed
        if ($invoiceData['date']) {
            $invoiceData['date'] = $this->formatDate($invoiceData['date']);
        }
        
        return $invoiceData;
    }

    /**
     * Extract CFOP from NFe
     *
     * @param array $infNfe
     * @return string
     */
    private function extractCfop($infNfe)
    {
        // Handle det with _value structure
        if (isset($infNfe['det'][0]['_value']['prod']['CFOP'])) {
            return $infNfe['det'][0]['_value']['prod']['CFOP'];
        }
        
        if (isset($infNfe['det'][0]['prod']['CFOP'])) {
            return $infNfe['det'][0]['prod']['CFOP'];
        }
        
        // Single item without array
        if (isset($infNfe['det']['_value']['prod']['CFOP'])) {
            return $infNfe['det']['_value']['prod']['CFOP'];
        }
        
        if (isset($infNfe['det']['prod']['CFOP'])) {
            return $infNfe['det']['prod']['CFOP'];
        }
        
        return '';
    }

    /**
     * Extract order number from NFe
     *
     * @param array $infNfe
     * @return string
     */
    private function extractOrderNumber($infNfe)
    {
        // Try to find order number in different possible locations
        $possibleLocations = [
            ['compra', 'xPed'],
            ['infAdic', 'infCpl'],
            ['cobr', 'fat', 'nFat']
        ];
        
        foreach ($possibleLocations as $path) {
            $value = $this->getNestedValue($infNfe, $path);
            if ($value) {
                // Extract order number using regex patterns
                if (preg_match('/\b\d{9}\b/', $value, $matches)) {
                    return $matches[0];
                }
                if (preg_match('/pedido[:\s]+(\d+)/i', $value, $matches)) {
                    return $matches[1];
                }
            }
        }
        
        return '';
    }

    /**
     * Get nested value from array
     *
     * @param array $array
     * @param array $keys
     * @return mixed|null
     */
    private function getNestedValue($array, $keys)
    {
        $value = $array;
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * Format date to MySQL format
     *
     * @param string $date
     * @return string
     */
    private function formatDate($date)
    {
        // NFe dates are usually in format: 2021-12-31T10:00:00-03:00
        $timestamp = strtotime($date);
        if ($timestamp) {
            return date('Y-m-d H:i:s', $timestamp);
        }
        return $date;
    }

    /**
     * Validate NFe XML structure
     *
     * @param string $xmlContent
     * @return bool
     * @throws LocalizedException
     */
    public function validateNfeXml($xmlContent)
    {
        try {
            $this->xmlParser->loadXML($xmlContent);
            $xmlArray = $this->xmlParser->xmlToArray();
            
            // Handle different NFe XML structures
            $nfe = null;
            $infNfe = null;
            
            // First check if we have nfeProc structure
            if (isset($xmlArray['nfeProc'])) {
                $nfeProc = $xmlArray['nfeProc'];
                // Check if nfeProc has _value (namespace)
                if (isset($nfeProc['_value']) && isset($nfeProc['_value']['NFe'])) {
                    $nfe = $nfeProc['_value']['NFe'];
                } elseif (isset($nfeProc['NFe'])) {
                    // NFe might be with namespace attributes
                    if (isset($nfeProc['NFe']['_value'])) {
                        $nfe = $nfeProc['NFe']['_value'];
                    } else {
                        $nfe = $nfeProc['NFe'];
                    }
                }
            } elseif (isset($xmlArray['NFe'])) {
                // Direct NFe structure
                if (isset($xmlArray['NFe']['_value'])) {
                    $nfe = $xmlArray['NFe']['_value'];
                } else {
                    $nfe = $xmlArray['NFe'];
                }
            }
            
            if (!$nfe) {
                throw new LocalizedException(__('Invalid NFe XML: Could not find NFe element'));
            }
            
            // Extract infNFe
            if (isset($nfe['infNFe']['_value'])) {
                $infNfe = $nfe['infNFe']['_value'];
            } elseif (isset($nfe['infNFe'])) {
                $infNfe = $nfe['infNFe'];
            }
            
            if (!$infNfe) {
                throw new LocalizedException(__('Invalid NFe XML: Could not find infNFe element'));
            }
            
            $required = ['ide', 'emit', 'dest', 'det', 'total'];
            foreach ($required as $element) {
                if (!isset($infNfe[$element])) {
                    throw new LocalizedException(__('Invalid NFe XML: Missing required element %1', $element));
                }
            }
            
            return true;
        } catch (\Exception $e) {
            throw new LocalizedException(__('NFe XML validation failed: %1', $e->getMessage()));
        }
    }
}