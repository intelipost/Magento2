# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.11.1] - 2025-12-04
### Fixed
- API Error while saving invoice protocol and invoice type

## [2.11.0] - 2025-11-25
### Added
- Added Pick-up options to shipping methods
- Added cellphone to shipment information

---
## [2.10.0] - 2025-09-22

### Added
- Added product's data to shipment array

---
## [2.9.1] - 2025-08-15

### Fixed
- Fixed issue with NFe XML import where certain XML structures caused parsing errors
- Add protocol and type to invoices.

---
## [2.9.0] - 2025-07-26

### Added
- New Web API endpoints for NFe (Nota Fiscal Eletrônica) import:
  - `POST /V1/intelipost/nfe/import` - Import single NFe XML
  - `POST /V1/intelipost/nfe/import-multiple` - Import multiple NFe XMLs in batch
  - `POST /V1/intelipost/nfe/validate` - Validate NFe XML without importing
- Manual NFe XML import feature in admin order view:
  - New textarea field for pasting NFe XML content
  - Import button to extract and populate invoice fields automatically
  - Support for parsing Brazilian NFe XML format with namespaces
- Automatic invoice synchronization with Intelipost API:
  - Invoices are automatically sent to Intelipost when saved
  - Uses the `/shipment_order/set_invoice` endpoint
  - Only sends if there's an associated Intelipost shipment for the order
- Console commands for manual cron job execution:
  - `php bin/magento intelipost:quotes:clear` - Delete old quote records
  - `php bin/magento intelipost:order:ready-for-shipment` - Process orders ready for shipment
  - `php bin/magento intelipost:order:ship` - Ship orders to Intelipost
  - `php bin/magento intelipost:order:create` - Create new orders in Intelipost

### Changed
- Invoice delete action now returns JSON response and triggers page reload to avoid registry issues
- Updated invoice repository to properly return the saved invoice object
