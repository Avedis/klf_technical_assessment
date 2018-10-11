<?php
class Order {

    public $discount = 0;
    public $tax_amount = 0;
    public $subtotal;
    public $total;

    public function __construct($subtotal)
    {
        $this->subtotal = $subtotal;
    }

    // applies coupon discounts and calculates taxes
    // returns total
    function calculate_total($province, $coupon_code = false)
    {
        //apply coupon before taxes
        if($coupon_code) {
            $this->calculate_coupon($coupon_code);
        }
        //calculate taxes
        $this->calculate_tax($province);
        // set and return total
        $this->total = $this->$subtotal - $this->$discount + $this->$tax_amount;
        return $this->total;
    }

    // calculate taxed amount using multiplier percentage i.e. 0.05
    private function calculate_tax($province)
    {
        include 'taxes.inc';

        switch($province) {
            case 'qc':
                $taxMultiplier = $tax['qc'][0] + $tax['qc'][1];
                $this->tax_amount = $subtotal * $taxMultiplier;
                break;
            case 'on':
                $taxMultiplier = $tax['on'][0];
                $this->tax_amount = $subtotal * $taxMultiplier;
                break;
            default:
                echo "Invalid province";
                break;
        }
    }

    // calculate coupon discount based on coupon code and type
    private function calculate_coupon($coupon_code)
    {
        include 'coupons.inc';

        $coupon = $coupons[$coupon_code];
        switch($coupon['type'])
        {
            case 'percentage':
                $this->discount = $this->subtotal * $coupon['value'];
                $this->subtotal = $this->subtotal - $discount;
                break;
            default:
                echo "Invalid coupon";
                break;
        }
    }
}
