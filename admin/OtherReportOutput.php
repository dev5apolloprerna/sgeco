<?php

/**
 * Add export-only table spacing without changing the browser report templates.
 *
 * TCPDF honours cell padding and line-height more reliably than CSS min-height.
 * Letting the cells grow with their content also prevents wrapped names from
 * overlapping the following row.
 */
function addOtherReportPdfSpacing($html)
{
    $style = '<style type="text/css">'
        . '.register-table tbody.register-body td,'
        . '.register-table tbody .data-row td {'
        . 'padding: 5px 3px; line-height: 1.25; vertical-align: middle;'
        . '}'
        . '.register-table tbody .data-row td.employee-text {'
        . 'padding-left: 7px;'
        . '}'
        . '</style>';

    // TCPDF can split a table row between pages when the remaining page
    // height is very small. Keeping each employee row intact prevents the
    // final row on one page from overlapping the first row on the next.
    $html = preg_replace(
        '/<tr\s+class=("|\')data-row\1(?![^>]*\bnobr=)[^>]*>/i',
        '<tr class="data-row" nobr="true">',
        $html
    );

    if (stripos($html, '</head>') !== false) {
        return preg_replace('/<\/head>/i', $style . '</head>', $html, 1);
    }

    return $style . $html;
}