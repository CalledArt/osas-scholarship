{{--
    The frame every scholarship PDF report shares: masthead, the scholarship
    and who prepared the report, the report's own body, then signature lines.

    Rendered by dompdf, which reads CSS 2.1 only, so layout is tables, not
    flex or grid. DejaVu Sans is bundled with dompdf and is the one font it
    carries with the peso sign and check marks. The footer is fixed so it
    repeats on every page; dompdf counts pages but cannot print the total.
    Colours are the application's own (DESIGN.md): Ink and Slate for text,
    Rule for lines, and a pending mark is Ink on Gold Wash, never gold text.
--}}
@props(['title', 'scholarship', 'preparedBy', 'generatedAt'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} — {{ $scholarship->title }}</title>
    <style>
        @page { margin: 36px 40px 56px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #10213A; }
        h1 { margin: 8px 0 0; font-size: 16px; color: #064532; }
        .institution { font-size: 11px; font-weight: bold; letter-spacing: 1px; color: #0B6E4F; text-transform: uppercase; }
        .office { margin-top: 2px; font-size: 10px; color: #3D4654; }
        .masthead { border-bottom: 2px solid #0B6E4F; padding-bottom: 10px; margin-bottom: 14px; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .meta td { padding: 2px 0; vertical-align: top; }
        .meta .label { width: 80px; color: #5A6472; }
        .summary { width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 14px; }
        .summary td { border: 1px solid #D8D4CB; padding: 7px 8px; vertical-align: top; }
        .summary .caption { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #5A6472; }
        .summary .figure { margin-top: 3px; font-size: 12px; font-weight: bold; }
        .summary .note { margin-top: 1px; color: #5A6472; }
        .notice { border: 1px solid #A8341F; background: #F7E4E0; color: #862715; padding: 7px 8px; margin-bottom: 14px; }
        .records { width: 100%; border-collapse: collapse; }
        .records th { background: #F4F2ED; border-bottom: 1px solid #8C8678; padding: 5px 6px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #3D4654; }
        .records td { border-bottom: 1px solid #D8D4CB; padding: 5px 6px; vertical-align: top; }
        .records tr { page-break-inside: avoid; }
        .records tfoot td { border-top: 1px solid #8C8678; border-bottom: none; font-weight: bold; }
        .number { text-align: right; white-space: nowrap; }
        .nowrap { white-space: nowrap; }
        .mark-green { color: #15803D; font-weight: bold; }
        .mark-gold { color: #10213A; background: #FDF3DA; font-weight: bold; }
        .mark-red { color: #862715; font-weight: bold; }
        .muted { color: #5A6472; }
        .signatures { width: 100%; margin-top: 36px; border-collapse: collapse; page-break-inside: avoid; }
        .signatures td { width: 50%; padding-right: 48px; vertical-align: bottom; }
        .signature-line { border-top: 1px solid #10213A; margin-top: 30px; padding-top: 3px; }
        footer { position: fixed; bottom: -36px; left: 0; right: 0; font-size: 8px; color: #5A6472; }
        footer table { width: 100%; border-collapse: collapse; }
        .page-number:after { content: counter(page); }
        {{ $styles ?? '' }}
    </style>
</head>
<body>
    <footer>
        <table>
            <tr>
                <td>{{ __('Generated :date by the OSAS Scholarship Allocation System', ['date' => $generatedAt->format('M j, Y g:i A')]) }}</td>
                <td class="number">{{ __('Page') }} <span class="page-number"></span></td>
            </tr>
        </table>
    </footer>

    <div class="masthead">
        <div class="institution">{{ __('Cavite State University') }}</div>
        <div class="office">{{ __('Office of Student Affairs and Services') }}</div>
        <h1>{{ $title }}</h1>
    </div>

    <table class="meta">
        <tr>
            <td class="label">{{ __('Scholarship') }}</td>
            <td><strong>{{ $scholarship->title }}</strong></td>
            <td class="label">{{ __('Generated') }}</td>
            <td>{{ $generatedAt->format('M j, Y g:i A') }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Type') }}</td>
            <td>{{ $scholarship->type->label() }} &middot; {{ $scholarship->status->label() }}</td>
            <td class="label">{{ __('Prepared by') }}</td>
            <td>{{ mb_strtoupper($preparedBy->surname_first_name) }}</td>
        </tr>
    </table>

    {{ $slot }}

    <table class="signatures">
        <tr>
            <td>
                <div class="muted">{{ __('Prepared by') }}</div>
                <div class="signature-line">{{ mb_strtoupper($preparedBy->surname_first_name) }}</div>
            </td>
            <td>
                <div class="muted">{{ __('Noted by') }}</div>
                <div class="signature-line">&nbsp;</div>
            </td>
        </tr>
    </table>
</body>
</html>
