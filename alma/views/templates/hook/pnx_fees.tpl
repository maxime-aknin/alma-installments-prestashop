{*
 * 2018-2020 Alma SAS
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2020 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *}

<style type="text/css">
    p.alma-fee-plan-details {
        line-height: 1.5em;
    }
</style>

<p>
    <b>
        {l s='You can offer monthly installments for amounts between %d€ and %d€.' sprintf=array($min_amount, $max_amount) mod='alma'}
    </b>
</p>

<p class="alma-fee-plan-details">
    {l s='Fees applied to each transaction for this plan:' mod='alma'}
    <br>
    {if $fee_plan["merchant_fee_variable"] > 0}
        <b>{l s='You pay:' mod='alma'}</b>
        {rtrim(sprintf("%.2f", $fee_plan["merchant_fee_variable"] / 100.0), '.0')|escape:'htmlall':'UTF-8'}%
    {/if}

    {if $fee_plan["merchant_fee_fixed"] > 0}
        {if $fee_plan["merchant_fee_variable"] == 0}
            <b>{l s='You pay:' mod='alma'}</b>
        {else}
            +
        {/if}
        {rtrim(sprintf("%.2f", $fee_plan["merchant_fee_fixed"] / 100.0), '.0')|escape:'htmlall':'UTF-8'}€
    {/if}

    {if $fee_plan["customer_fee_variable"] > 0}
        <br>
        <b>{l s='Customers pay:' mod='alma'}</b>
        {rtrim(sprintf("%.2f", $fee_plan["customer_fee_variable"] / 100.0), '.0')|escape:'htmlall':'UTF-8'}%
    {/if}

    {if $fee_plan["customer_fee_fixed"] > 0}
        {if $fee_plan["customer_fee_variable"] == 0}
            <br>
            <b>{l s='Customers pay:' mod='alma'}</b>
        {else}
            +
        {/if}
        {rtrim(sprintf("%.2f", $fee_plan["customer_fee_fixed"] / 100.0), '.0')|escape:'htmlall':'UTF-8'}€
    {/if}

    <br><br>
    <a href='mailto:contact@getalma.eu?subject={l s='Fees for %d-installment plan' sprintf=$fee_plan['installments_count'] mod='alma'}'>{l s='Contact us' mod='alma'}</a>
    {l s='if you think your sales volumes warrant better rates!' mod='alma'}
</p>
