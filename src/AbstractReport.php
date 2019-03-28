<?php

abstract class AbstractReport
{
    /**
     * @var SourceClientInterface
     */
    protected $_client;

    /**
     * @var array
     */
    protected $_data = [];

    /**
     * AbstractReport constructor.
     * @param SourceClientInterface $client
     */
    public function __construct(SourceClientInterface $client)
    {
        $this->_client = $client;
    }

    public function make()
    {
        $this->_setData();
        $clientData = $this->_client->fetchAll();
        if (!count($clientData)) {
            return;
        }
        $this->_calculateMetrics($clientData);
    }

    /**
     * @return array
     */
    public function data(): array
    {
        return $this->_data;
    }

    /**
     * @return mixed
     */
    abstract protected function _setData();

    /**
     * @param array $data
     * @return mixed
     */
    abstract protected function _calculateMetrics(array $data);
}