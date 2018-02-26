<?php

namespace PagarMe\Sdk\Transaction\Request;

use PagarMe\Sdk\RequestInterface;
use PagarMe\Sdk\SplitRule\SplitRuleCollection;

class TransactionCapture implements RequestInterface
{
    /**
     * @var int
     */
    protected $transaction;
    /**
     * @var int
     */
    protected $amount;
    /**
     * @var array
     */
    protected $metadata;
    /**
     * @var PagarMe\Sdk\SplitRule\SplitRuleCollection
     */
    protected $splitRules;

    /**
     * @param PagarMe\Sdk\Transaction\Transaction $transaction
     * @param int $amount
     * @param array $metadata
     * @param PagarMe\Sdk\SplitRule\SplitRuleCollection $splitRules
     */
    public function __construct($transaction, $amount, $metadata = [], $splitRules = null)
    {
        $this->transaction = $transaction;
        $this->amount = $amount;
        $this->metadata = $metadata;
        $this->splitRules = $splitRules;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        $payload = [];

        if (!is_null($this->amount)) {
            $payload['amount'] = $this->amount;
        }

        if (!empty($this->metadata)) {
            $payload['metadata'] = $this->metadata;
        }

        if (!is_null($this->splitRules)) {
            $payload['split_rules'] = $this->getSplitRulesInfo(
                $this->splitRules
            );
        }

        return $payload;
    }

    /**
     * @return mixed
     */
    protected function getTransactionId()
    {
        $transactionId = $this->transaction->getId();

        if ($transactionId) {
            return $transactionId;
        }

        return $this->transaction->getToken();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return sprintf('transactions/%s/capture', $this->getTransactionId());
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return self::HTTP_POST;
    }

    /**
     * @param \PagarMe\Sdk\SplitRule\SplitRuleCollection $splitRules
     * @return array
     */
    private function getSplitRulesInfo(SplitRuleCollection $splitRules)
    {
        $rules = [];

        foreach ($splitRules as $key => $splitRule) {
            $rule = [
                'recipient_id'          => $splitRule->getRecipient()->getId(),
                'charge_processing_fee' => $splitRule->getChargeProcessingFee(),
                'liable'                => $splitRule->getLiable()
            ];

            $rules[$key] = array_merge($rule, $this->getRuleValue($splitRule));
        }

        return $rules;
    }

    /**
     * @param \PagarMe\Sdk\SplitRule\SplitRule $splitRule
     * @return array
     */
    private function getRuleValue($splitRule)
    {
        if (!is_null($splitRule->getAmount())) {
            return ['amount' => $splitRule->getAmount()];
        }

        return ['percentage' => $splitRule->getPercentage()];
    }
}
