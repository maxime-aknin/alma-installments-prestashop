<?php
use Alma\PrestaShop\Model\PaymentData;


test('eligiblityDataFromEmpty', function() {
    $this->assertEquals([
            'purchase_amount' => 0,
            'queries' => []
        ],
        PaymentData::eligibilityDataFrom([], 0)
    );
});

test('eligiblityDataFromPurshaseAmount0', function() {
    $this->assertEquals([
            'purchase_amount' => 0,
            'queries' => [
                [
                    'purchase_amount' => 0,
                    'installments_count' => 2,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ], [
                    'purchase_amount' => 0,
                    'installments_count' => 3,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ],[
                    'purchase_amount' => 0,
                    'installments_count' => 4,
                    'deferred_days' => 0,
                    'deferred_months' => 0,
                ],
            ]
        ],
        PaymentData::eligibilityDataFrom(['todo'], 0)
    );
});
