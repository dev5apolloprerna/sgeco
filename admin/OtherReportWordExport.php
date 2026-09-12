<?php

/**
 * Combine rendered form pages into a Word-compatible HTML document.
 *
 * Word can open HTML delivered with a .doc extension. Keeping the report's
 * existing HTML and CSS also makes the Word layout match its PDF preview more
 * closely than converting the form into spreadsheet cells.
 */
function buildOtherReportWordDocument(array $pages, $title, $pageSelector)
{
    if (!$pages) {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' .
            htmlspecialchars($title, ENT_QUOTES, 'UTF-8') .
            '</title></head><body><p>No Data Found !</p></body></html>';
    }

    $firstPage = (string) $pages[0];
    preg_match('/<style\b[^>]*>(.*?)<\/style>/is', $firstPage, $styleMatch);
    $style = isset($styleMatch[1]) ? $styleMatch[1] : '';
    $bodies = array();

    foreach ($pages as $page) {
        if (preg_match('/<body\b[^>]*>(.*?)<\/body>/is', (string) $page, $bodyMatch)) {
            $bodies[] = $bodyMatch[1];
        }
    }

    $wordStyle = '@page{size:A4;margin:0}' .
        'body{margin:0;padding:0;background:#fff}' .
        // Do not turn layout tables into bordered Word tables. Form VIII in
        // particular must remain borderless; its underlines are drawn by the
        // spans in the supplied template instead.
        'table,table td{border:0;mso-border-alt:none}' .
        $pageSelector . '{margin:0 auto;page-break-inside:avoid}';

    // A separate empty break can be pulled onto the preceding fixed-height
    // form by Word, causing the next employee's heading to appear at the foot
    // of that page. Applying the break directly to the next form keeps every
    // employee together from the first record onward.
    foreach ($bodies as $index => $body) {
        if ($index === 0) continue;
        $bodies[$index] = preg_replace(
            '/(<(?:div|section)\b[^>]*class\s*=\s*(["\'])[^"\']*' .
                preg_quote(ltrim($pageSelector, '.'), '/') . '\b[^"\']*\2)([^>]*>)/i',
            '$1 style="page-break-before:always;mso-break-type:page-break"$3',
            $body,
            1
        );
    }

    return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' .
        htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title><style>' .
        $style . $wordStyle . '</style></head><body>' . implode("\n", $bodies) .
        '</body></html>';
}

function outputOtherReportWordDocument($html, $filename)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/msword; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    echo "\xEF\xBB\xBF" . $html;
    exit;
}
