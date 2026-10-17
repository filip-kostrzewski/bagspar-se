<dl class="quote-totals">
    <div>
        <dt>Summa exkl. moms</dt>
        <dd>{{ \App\Support\Kronor::format($quote->subtotalOre()) }}</dd>
    </div>
    <div>
        <dt>Moms 25 %</dt>
        <dd>{{ \App\Support\Kronor::format($quote->vatOre()) }}</dd>
    </div>
    <div class="quote-totals__total">
        <dt>Att betala inkl. moms</dt>
        <dd>{{ \App\Support\Kronor::format($quote->totalOre()) }}</dd>
    </div>
</dl>
