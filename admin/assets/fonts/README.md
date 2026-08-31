# Gujarati fonts used by PDF exports

TCPDF can only print characters that exist in an embedded font. Its bundled
FreeSans font does **not** contain Gujarati glyphs, so using it turns Gujarati
text into missing-glyph boxes or question marks.

Download the open-source **Noto Sans Gujarati** family from the official Noto
Gujarati repository and place these two files in this directory:

* `NotoSansGujarati-Regular.ttf`
* `NotoSansGujarati-Bold.ttf`

The application registers and embeds these files when it creates the Full and
Final Settlement PDF. Keep the exact filenames above. The web-server user must
also be able to write to `admin/tcpdf/fonts`, because TCPDF creates its cached
font definition files there the first time the export runs.

Do not commit proprietary fonts such as Shruti unless their licence explicitly
permits redistribution.