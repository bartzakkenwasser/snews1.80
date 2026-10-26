[DATABASE]
; database engine (mysql, sqlite)
engine = "mysql"

; Correct DSN with charset
dsn = "mysql:host=localhost;dbname=snews18;charset=utf8mb4"

; Database User
user = "root"

; Database Password
pass = "geheim"

; Database Prefix
prefix = ""

[SECURITY]
; KEY SECURE_ID. Wordt door snews.php geladen; mag niet leeg zijn.
SECURE_ID = "1234"


; Jouw HASH String voor sessiebeveiliging
string_hash = "use_silly_strings"


[OPTIONS]
; page_list volgorde (home, archive, contact, sitemap, pages)
page_list = "home,archive,contact,sitemap,pages"

; Pagina's die je wilt verbergen in het menu (kommagescheiden seftitles)
ignore_pages = ""

; Categorieën die je wilt verbergen in het menu
ignore_cats = ""

; Divider character (HTML-entity veilig tussen dubbele quotes)
divider = "&middot;"

; Gebruikt in artikel paginering links
paginator = "p_"

; Gebruikt in commentaar paginering links
comment_pages = "c_"


[INFO_TAGS]
; Infoline template
infoline = '<p class="date">,readmore,comments,date,edit,</p>'

; Comments template
comments = '<p class="meta">,name, $on ,date,edit,</p>,<p class="comment">,comment,</p>'
