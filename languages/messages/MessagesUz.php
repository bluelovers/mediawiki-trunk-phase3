<?php
/**
 * Uzbek (O'zbek)
 * Translated by: Behzod Saidov <behzodsaidov@gmail.com>
 * 
 * **********************************************************
 * Iltimos, MediaWiki tarjimalari bilan bo'g'liq taklif va
 * mulohazalaringiz bo'lsa men bilan e-mail orqali bo'g'laning
 * **********************************************************
 */

$fallback8bitEncoding = 'windows-1252';

$linkPrefixExtension = true;

$namespaceNames = array(
	NS_MEDIA            => 'Mediya',
	NS_SPECIAL          => 'Maxsus',
	NS_MAIN             => 'Asosiy',
	NS_TALK             => 'Munozara',
	NS_USER             => 'Foydalanuvchi',
	NS_USER_TALK        => 'Foydalanuvchi_munozarasi',
	# NS_PROJECT set by $wgMetaNamespace
	NS_PROJECT_TALK     => '$1_munozarasi',
	NS_IMAGE            => 'Tasvir',
	NS_IMAGE_TALK       => 'Tasvir_munozarasi',
	NS_MEDIAWIKI        => 'MediyaViki',
	NS_MEDIAWIKI_TALK   => 'MediyaViki_munozarasi',
	NS_TEMPLATE         => 'Shablon',
	NS_TEMPLATE_TALK    => 'Shablon_munozarasi',
	NS_HELP             => 'Yordam',
	NS_HELP_TALK        => 'Yordam_munozarasi',
	NS_CATEGORY         => 'Kategoriya',
	NS_CATEGORY_TALK    => 'Kategoriya_munozarasi',
);
	
$linkTrail = '/^([a-zʻʼ“»]+)(.*)$/sDu';

$messages = array(
# Dates
'sunday'        => 'Yakshanba',
'monday'        => 'Dushanba',
'tuesday'       => 'Seshanba',
'wednesday'     => 'Chorshanba',
'thursday'      => 'Payshanba',
'friday'        => 'Juma',
'saturday'      => 'Shanba',
'sun'           => 'Yak',
'mon'           => 'Dsh',
'tue'           => 'Ssh',
'wed'           => 'Chr',
'thu'           => 'Pay',
'fri'           => 'Jum',
'sat'           => 'Shn',
'january'       => 'yanvar',
'february'      => 'fevral',
'march'         => 'mart',
'april'         => 'aprel',
'may_long'      => 'may',
'june'          => 'iyun',
'july'          => 'iyul',
'august'        => 'avgust',
'september'     => 'sentabr',
'october'       => 'oktabr',
'november'      => 'noyabr',
'december'      => 'dekabr',
'january-gen'   => 'yanvarning',
'february-gen'  => 'fevralning',
'march-gen'     => 'martning',
'april-gen'     => 'aprelning',
'may-gen'       => 'mayning',
'june-gen'      => 'iyunning',
'july-gen'      => 'iyulning',
'august-gen'    => 'avgustning',
'september-gen' => 'sentabrning',
'october-gen'   => 'oktabrning',
'november-gen'  => 'noyabrning',
'december-gen'  => 'dekabrning',
'jan'           => 'yan',
'feb'           => 'fev',
'mar'           => 'mar',
'apr'           => 'apr',
'may'           => 'may',
'jun'           => 'iyn',
'jul'           => 'iyl',
'aug'           => 'avg',
'sep'           => 'sen',
'oct'           => 'okt',
'nov'           => 'noy',
'dec'           => 'dek',

# Bits of text used by many pages
'categories'      => 'Kategoriyalar',
'pagecategories'  => '{{PLURAL:$1|Kategoriya|Kategoriyalar}}',
'category_header' => '"$1" kategoriyadagi maqolalar.',
'subcategories'   => 'Podkategoriyalar',

'linkprefix' => '/^(.*?)([a-zA-Z\x80-\xffʻʼ«„]+)$/sDu',

'about'          => 'Haqida',
'newwindow'      => '(yangi oyanada ochiladi)',
'cancel'         => 'Voz kech',
'qbedit'         => 'Tahrirlash',
'qbspecialpages' => 'Maxsus sahifalar',
'mytalk'         => 'Mening suhbatim',
'anontalk'       => 'Bu IP uchun suhbat',
'navigation'     => 'Saytda harakatlanish',

'returnto'         => '$1 sahifasiga qaytish.',
'help'             => 'Yordam',
'search'           => 'Qidirish',
'history'          => 'Sahifa tarixi',
'history_short'    => 'Tarix',
'printableversion' => 'Bosma uchun versiya',
'permalink'        => "Doimiy bog'",
'edit'             => 'Tahrirlash',
'delete'           => "O'chirish",
'protect'          => 'Himoyalash',
'protectthispage'  => 'Bu sahifani himoyala',
'unprotect'        => 'Himoyadan chiqarish',
'specialpage'      => 'Maxsus sahifa',
'talk'             => 'Munozara',
'views'            => "Ko'rinishlar",
'toolbox'          => 'Asboblar',
'otherlanguages'   => 'Boshqa tillarda',
'lastmodifiedat'   => 'Bu sahifa oxirgi marta $2, $1 sanasida tahrirlangan.', # $1 date, $2 time
'viewcount'        => 'Bu sahifaga {{plural:$1|bir marta|$1 marta}} murojaat qilingan.',
'jumptosearch'     => 'Qidir',

# All link text and link target definitions of links into project namespace that get used by other message strings, with the exception of user group pages (see grouppage) and the disambiguation template definition (see disambiguations).
'aboutsite'         => '{{SITENAME}} haqida',
'aboutpage'         => 'Project:Haqida',
'currentevents'     => 'Joriy hodisalar',
'currentevents-url' => 'Project:Joriy hodisalar',
'edithelp'          => 'Tahrirlash yordami',
'edithelppage'      => 'Help:Tahrirlash',
'helppage'          => 'Help:Mundarija',
'mainpage'          => 'Bosh sahifa',
'portal'            => 'Jamoa portali',
'portal-url'        => 'Project:Jamoa portali',
'privacy'           => 'Konfidensiallik siyosati',
'privacypage'       => 'Project:Konfidensiallik siyosati',
'sitesupport'       => "Loyihaga ko'mak",
'sitesupport-url'   => "Project:Loyihaga ko'mak",

'editsection' => 'tahrirlash',
'toc'         => 'Mundarija',
'showtoc'     => "Ko'rsatish",
'hidetoc'     => 'yashirish',

# Short words for each namespace, by default used in the 'article' tab in monobook
'nstab-main'     => 'Maqola',
'nstab-user'     => 'Foydalanuvchi sahifasi',
'nstab-special'  => 'Maxsus',
'nstab-project'  => 'Loyiha sahifasi',
'nstab-image'    => 'Fayl',
'nstab-template' => 'Shablon',
'nstab-help'     => 'Yordam sahifasi',
'nstab-category' => 'Kategoriya',

# General errors
'viewsource'        => "Ko'rib chiqish",
'protectedpagetext' => 'Bu sahifa tahrirlashdan saqlanish maqsadida qulflangan.',
'viewsourcetext'    => "Siz bu sahifaning manbasini ko'rishingiz va uni nusxasini olishingiz mumkin:",

# Login and logout pages
'yourname'           => 'Foydalanuvchi nomi',
'yourpassword'       => "Maxfiy so'z",
'yourpasswordagain'  => "Maxfiy so'zni qayta kiriting",
'remembermypassword' => "Hisob ma'lumotlarini shu kompyuterda eslab qolish",
'login'              => 'Kirish',
'loginprompt'        => "{{SITENAME}}ga kirish uchun kukilar yoqilgan bo'lishi kerak.",
'userlogin'          => 'Kirish / Hisob yaratish',
'userlogout'         => 'Chiqish',
'nologin'            => "Hisobingiz yo'q-mi? $1.",
'nologinlink'        => 'Hisob yaratish',
'createaccount'      => 'Hisob yaratish',
'gotaccount'         => 'Hisobingiz bor-mi? $1.',
'gotaccountlink'     => 'Kirish',
'yourrealname'       => 'Haqiqiy ism *:',
'loginsuccesstitle'  => 'Kirish muvaffaqiyatli amalga oshdi',
'loginsuccess'       => "'''{{SITENAME}}ga \"\$1\" foydalanuvchi nomi bilan kirdingiz.'''",

# Edit pages
'summary'        => 'Qisqa izoh',
'minoredit'      => 'Bu kichik tahrir',
'watchthis'      => 'Sahifani kuzatish',
'savearticle'    => 'Saqlash',
'preview'        => "Ko'rib chiqish",
'showpreview'    => "Ko'rib chiqish",
'showdiff'       => "O'zgarishlarni ko'rsatish",
'newarticletext' => "Bu sahifa hali mavjud emas. Sahifani yaratish uchun quyida matn kiritishingiz mumkin (qo'shimcha axborot uchun [[Help:Mundarija|yordam sahifasini]] ko'ring). Agar bu sahifaga xatolik sabab kelgan bo'lsangiz brauzeringizning '''orqaga''' tugmasini bosing.",
'noarticletext'  => "Bu sahifada hozircha hech qanday matn yo'q. Siz bu sarlavhani boshqa sahifalardan [[Special:Search/{{PAGENAME}}|qidirishingiz]] yoki bu sahifani [{{fullurl:{{FULLPAGENAME}}|action=edit}} tahrirlashingiz] mumkin.",
'clearyourcache' => "'''Etibor bering:''' O'zgartirishlaringiz ko'rish uchun, yangi moslamalaringizning saqlashdan keyin, brauser keshini tozalash kerak:<br />
'''Mozilla / Firefox:''' ''Ctrl+Shift+R'', '''IE:''' ''Ctrl+F5'', '''Safari:''' ''Cmd+Shift+R'', '''Konqueror:''' ''F5'', '''Opera:''' ''Tools → Preferences'' orqali keshni tozalang.",
'previewnote'    => "<strong>Bu shunchaki ko'rib chiqish. O'zgarishlar hali saqlangani yo'q!</strong>",
'editing'        => '$1 tahrirlanmoqda',

# History pages
'next' => 'keyingi',

# Search results
'searchresults'    => 'Qidiruv natijalari',
'searchresulttext' => "{{SITENAME}}da qidirish haqida qo'shimcha ma'lumotga ega bo'lishini xoxlasangiz, [[{{ns:project}}:Qidiruv|{{SITENAME}}da qidiruv]] sahifasini o'qing.",
'noexactmatch'     => "'''\"\$1\" nomli birorta ham sahifa yo'q.''' Bu sahifani [[:\$1|yaratishingiz]] mumkin.",
'prevn'            => 'oldingi $1',
'nextn'            => 'keyingi $1',
'viewprevnext'     => "Ko'rish ($1) ($2) ($3).",
'showingresults'   => "#<b>$2</b> boshlanayotgan <b>$1</b> natijalar ko'rsatilyapti.",
'powersearch'      => 'Qidiruv',

# Preferences page
'preferences'       => 'Moslamalar',
'mypreferences'     => 'Mening moslamalarim',
'skin'              => 'Tashqi bezash',
'math'              => 'Formulalar',
'datetime'          => 'Sana va vaqt',
'prefs-personal'    => 'Shaxsiy maʼlumotlar',
'prefs-rc'          => "Yangi o'zgartirishlar",
'prefs-watchlist'   => "Kuzatuv ro'yxati",
'prefs-misc'        => 'Boshqa moslamalar',
'saveprefs'         => 'Saqlash',
'resetprefs'        => 'Bekor qilish',
'textboxsize'       => 'Tahrirlash',
'searchresultshead' => 'Qidiruv natijalari',
'files'             => 'Fayllar',

# Recent changes
'recentchanges'     => "Yangi o'zgartirishlar",
'recentchangestext' => "Bu sahifada siz oxirgi o'zgartirishlarni ko'rishingiz mumkin.",
'rcnote'            => "Pastda oxirgi '''$2''' kun davomida sodir bo'lgan $1 o'zgartirishlar ko'rsatilgan. ($3)<!--Below are the last <strong>$1</strong> changes in the last <strong>$2</strong> days, as of $3.-->",
'rclistfrom'        => "$1dan boshlab yangi o'zgartirishlarni ko'rsat.",
'rcshowhideminor'   => 'Kichkina tahrirlarni $1',
'rcshowhidebots'    => 'Botlarni $1',
'rcshowhideliu'     => 'Tanishtirilgan foydalanuvchilarni $1',
'rcshowhideanons'   => 'Anonim foydalanuvchilarni $1',
'rcshowhidemine'    => 'Tahrirlarimni $1',
'rclinks'           => "Oxirgi $2 kun davomida sodir bo'lgan $1 o'zgartirishlarni ko'rsat.<br />$3",
'diff'              => 'farq',
'hist'              => 'tarix',
'hide'              => 'yashir',
'show'              => "ko'rsat",
'minoreditletter'   => 'k',
'newpageletter'     => 'Y',
'boteditletter'     => 'b',
'sectionlink'       => '→',

# Recent changes linked
'recentchangeslinked' => "Bogʻlangan o'zgarishlar",

# Upload
'upload'     => 'Fayl yuklash',
'uploadtext' => "Ushbu formani fayl upload qilish uchun ishlating. Avvalgi upload qilingang tasvirlarni ko\'rish yoki qidirish uchun [[Special:Imagelist|upload qilingan tasvirlar ruyxati]] sahifasini ochin. Tasvir uploadlar va yoqotishlar [[Special:Log/upload|upload log]]da hamda ko\'rsatiladi.

Tasvirni sahifa ichiga urnash uchun, shu forma belgilarni ishlating
'''[[{{ns:image}}:File.jpg]]''',
'''[[{{ns:image}}:File.png|alt text]]'''yoki faqat yoborish belgisi uchun
'''[[{{ns:media}}:File.ogg]]'''  ishlating.",

# Image list
'ilsubmit' => 'Qidir',

'disambiguationspage' => '{{ns:template}}:Disambig',

# Miscellaneous special pages
'ncategories'      => '$1 {{PLURAL:$1|kategoriya|kategoriyalar}}',
'wantedcategories' => 'Talab qilinayotgan kategoriyalar',
'mostcategories'   => "Eng ko'p kategoriyalarli sahifalar",
'allpages'         => 'Barcha sahifalar',
'randompage'       => 'Tasodifiy sahifa',
'specialpages'     => 'Maxsus sahifalar',
'move'             => "Ko'chirish",

'categoriespagetext' => 'Ushbu kategoriyalar vikida bor.',

# Special:Allpages
'allarticles'  => 'Barcha sahifalar',
'allpagesnext' => 'Keyingi',

# Watchlist
'watchlist'     => "Mening kuzatuv ro'yxatim",
'my-watchlist'     => "Mening kuzatuv ro'yxatim",
'watchlistfor'  => "('''$1''' uchun)",
'watch'         => 'kuzat',
'watchthispage' => "Sahifani ko'zat",
'unwatch'       => 'kuzatmaslik',
'wlnote'        => "Pastda oxirgi '''$2''' soatda sodir bo\'lgan $1 o\'zgartirishlar ko\'rsatilgan.",

# Delete/protect/revert
'deletecomment'   => "O'chirish sababi",
'protectmoveonly' => "Faqat ko'chirishdan himoyala",

# Restrictions (nouns)
'restriction-edit' => 'Tahrirlash',

# Namespace form on various pages
'namespace' => 'Nomlar fazosi:',
'invert'    => 'Tanlashning tartibini almashtir',

# Contributions
'contributions' => 'Foydalanuvchining hissasi',
'mycontris'     => 'mening hissam',

# What links here
'whatlinkshere' => "Bu sahifaga bog'langan sahifalar",
'linklistsub'   => "(Bog'lanishlar ro'yxati)",
'linkshere'     => "Quyidagi sahifalar '''[[:$1]]''' sahifasiga bog'langan:",
'nolinkshere'   => "'''[[:$1]]''' sahifasiga hech qaysi sahifa bog'lanmagan.",

# Move page
'movearticle' => "Sahifani ko'chir",
'1movedto2'   => "[[$1]] [[$2]]ga ko'chirildi",

# Tooltip help for the actions
'tooltip-pt-userpage'             => 'Mening foydalanuvchi sahifam',
'tooltip-pt-anonuserpage'         => 'Siznig ip manzilingiz foydalanuvchi sahifasi',
'tooltip-pt-mytalk'               => 'Mening suhbat sahifam',
'tooltip-pt-anontalk'             => 'Bu ip manzildan amalga oshirilgan tahrirlar munozarasi',
'tooltip-pt-preferences'          => 'Mening moslamalarim',
'tooltip-pt-watchlist'            => "Siz kuzatib borayotgan sahifalar ro\'yxati.",
'tooltip-pt-mycontris'            => "Mening hissa qo'shgan sahifalarim ro'yxati",
'tooltip-pt-login'                => "Bu majburiyat bo'lmasada, kirishingiz taklif qilinadi.",
'tooltip-pt-anonlogin'            => "Bu majburiyat bo'lmasada, kirishingiz taklif qilinadi.",
'tooltip-pt-logout'               => 'Chiqish',
'tooltip-ca-talk'                 => 'Sahifa matni borasida munozara',
'tooltip-ca-edit'                 => "Siz bu sahifani tahrirlashingiz mumkin. Iltimos, saqlashdan oldim ko'rib chiqish tugmasidan foydalaning.",
'tooltip-ca-addsection'           => "Bu munozaraga izoh qo'shish.",
'tooltip-ca-viewsource'           => "Bu sahifa himoyalangan. Siz uning manbasini ko'rishingiz mumkin.",
'tooltip-ca-history'              => 'Bu sahifaning oldingi versiyalari.',
'tooltip-ca-protect'              => 'Bu sahifani himoyalash',
'tooltip-ca-delete'               => "Bu sahifani o'chirish",
'tooltip-ca-undelete'             => "Bu sahifa o'chirilmasdan oldin qilingan tahrirlarni tiklash",
'tooltip-ca-move'                 => "Bu sahifani ko'chirish",
'tooltip-ca-watch'                => "Bu sahifani kuzatuv ro'yxatingizga qo'shish",
'tooltip-ca-unwatch'              => "Bu sahifani kuzatuv ro'yxatingizga o'chirish",
'tooltip-search'                  => '{{SITENAME}}da qidirish',
'tooltip-p-logo'                  => 'Bosh sahifa',
'tooltip-n-mainpage'              => "Bosh sahifaga o'tish",
'tooltip-n-portal'                => 'Loyiha haqida, nimalar qilishingiz mumkin, nimalarni qayerdan topish mumkin',
'tooltip-n-currentevents'         => "Joriy hodisalar haqida ma'lumot olish",
'tooltip-n-recentchanges'         => "Yangi o'zgarishlar ro'yxati.",
'tooltip-n-randompage'            => 'Tasodifiy sahifani yuklash',
'tooltip-n-help'                  => "O'rganish uchun manzil.",
'tooltip-n-sitesupport'           => "Bizni qo'llab quvvatlang.",
'tooltip-t-whatlinkshere'         => "Bu sahifaga bog'langan sahifalar ro'yxati",
'tooltip-t-recentchangeslinked'   => "Bu sahifa bog'langan sahifalardagi yangi o'zgarishlar",
'tooltip-feed-rss'                => "Bu sahifa uchun RSS ta'minot",
'tooltip-feed-atom'               => "Bu sahifa uchun Atom ta'minot",
'tooltip-t-contributions'         => "Bu foydalanuvchinig qo'shgan hissasini ko'rish",
'tooltip-t-emailuser'             => "Bu foydalanuvchiga xat jo'natish",
'tooltip-t-upload'                => 'Rasmlar yoki media fayllar yuklash',
'tooltip-t-specialpages'          => "Maxsus sahifalar ro'yxati",
'tooltip-ca-nstab-main'           => "Sahifani ko'rish",
'tooltip-ca-nstab-user'           => "Foydalanuvchi sahifasini ko'rish",
'tooltip-ca-nstab-media'          => "Media sahifasini ko'rish",
'tooltip-ca-nstab-special'        => 'Bu maxsus sahifa, uni tahrirlay olmaysiz.',
'tooltip-ca-nstab-project'        => "Loyiha sahifasini ko'rish",
'tooltip-ca-nstab-image'          => "Rasm sahifasini ko'rish",
'tooltip-ca-nstab-mediawiki'      => "Tizim xabarini ko'rish",
'tooltip-ca-nstab-template'       => "Shablonni ko'rish",
'tooltip-ca-nstab-help'           => "Yordam sahifasini ko'rish",
'tooltip-ca-nstab-category'       => "Kategoriya sahifasini ko'rish",
'tooltip-minoredit'               => "Kichik o'zgarish sifatida belgilash",
'tooltip-save'                    => "O'zgarishlarni saqlash",
'tooltip-preview'                 => "O'zgarishlarni saqlash. Iltimos saqlashdan oldin uni ishlating!",
'tooltip-diff'                    => "Matnga qanday o'zgarishlar kiritganligingizni ko'rish.",
'tooltip-compareselectedversions' => "Bu sahifaning ikki tanlangan versiyalari o'rtasidagi farqni ko'rish.",
'tooltip-watch'                   => "Bu sahifani kuzatuv ro'yxatingizga qo'shish",
'tooltip-recreate'                => "Bu sahifani u o'chirilgan bo'lishiga qaramasdan qayta yaratish",

# Attribution
'and' => 'va',

# Spam protection
'subcategorycount'     => 'Bu kategoriya {{PLURAL:$1|bir|$1}} podkategoriyadan iborat.',
'categoryarticlecount' => 'Bu kategoriyada {{PLURAL:$1|bitta|$1}} sahifa bor.',

# Media information
'imagemaxsize' => 'Tavsif sahifalaridagi tasvir kattaligi:',
'thumbsize'    => 'Tasvir kichiklashgan versiyasining kattaligi:',

# Inputbox extension, may be useful in other contexts as well
'createarticle' => 'Maqola kiritish',

'unit-pixel' => 'piksel',

);

?>
