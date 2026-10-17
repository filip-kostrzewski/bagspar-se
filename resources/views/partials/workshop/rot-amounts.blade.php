<p>Arbetskostnaden är kostnaden för den registrerade arbetstiden. Beloppen på raderna är exkl. moms.</p>
<table class="material-sheet">
    <caption>Belopp</caption>
    <thead>
        <tr>
            <th scope="col">Post</th>
            <th scope="col">Exkl. moms</th>
            <th scope="col">Moms 25 %</th>
            <th scope="col">Inkl. moms</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th scope="row">Arbetskostnad</th>
            @if ($basis['labor_amounts']['net'] === null)
                <td>Timpris saknas</td>
                <td>Timpris saknas</td>
                <td>Timpris saknas</td>
            @else
                <td>{{ \App\Support\Kronor::format($basis['labor_amounts']['net']) }}</td>
                <td>{{ \App\Support\Kronor::format($basis['labor_amounts']['vat']) }}</td>
                <td>{{ \App\Support\Kronor::format($basis['labor_amounts']['gross']) }}</td>
            @endif
        </tr>
        <tr>
            <th scope="row">Material</th>
            <td>{{ \App\Support\Kronor::format($basis['material_amounts']['net']) }}</td>
            <td>{{ \App\Support\Kronor::format($basis['material_amounts']['vat']) }}</td>
            <td>{{ \App\Support\Kronor::format($basis['material_amounts']['gross']) }}</td>
        </tr>
        <tr>
            <th scope="row">Totalt (arbetskostnad + material)</th>
            @if ($basis['total_amounts']['net'] === null)
                <td colspan="3">Kan inte räknas</td>
            @else
                <td>{{ \App\Support\Kronor::format($basis['total_amounts']['net']) }}</td>
                <td>{{ \App\Support\Kronor::format($basis['total_amounts']['vat']) }}</td>
                <td>{{ \App\Support\Kronor::format($basis['total_amounts']['gross']) }}</td>
            @endif
        </tr>
    </tbody>
</table>
