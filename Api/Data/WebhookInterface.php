<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) Intelipost
 * @author      Alex Restani <alex.restani@intelipost.com.br>
 */

namespace Intelipost\Shipping\Api\Data;

interface WebhookInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'entity_id';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const STATUS = 'status';
    const MESSAGE = 'message';
    const PAYLOAD = 'payload';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set EntityId.
     * @param $entityId
     */
    public function setEntityId($entityId);

    /**
     * Get IncrementID.
     *
     * @return string
     */
    public function getOrderIncrementId();

    /**
     * Set IncrementId.
     * @param $incrementId
     */
    public function setOrderIncrementId($incrementId);

    /**
     * Get Status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set Status.
     * @param $status
     */
    public function setStatus($status);

    /**
     * Get Status.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set Status.
     * @param $message
     */
    public function setMessage($message);

    /**
     * Get Payload.
     *
     * @return string
     */
    public function getPayload();

    /**
     * Set Payload.
     * @param $payload
     */
    public function setPayload($payload);

    /**
     * Get CreatedAt.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set CreatedAt.
     * @param $createdAt
     */
    public function setCreatedAt($createdAt);

}
