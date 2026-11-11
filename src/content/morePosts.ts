import type { Lang, Post } from './types';

/**
 * مطلب‌های تازهٔ بلاگ. فقط از اطلاعاتی استفاده شده که در خود سایت (درباره، تجربه‌ها، تقویم، کتابخانه) آمده است.
 * ترتیب: تازه‌ترین اول.
 */
const S = 'https://heleyleh-bu.ir';

export const extraPosts: Record<Lang, Post[]> = {
  fa: [
    {
      id: 'travel-guide',
      slug: 'helileh-bushehr-travel-guide',
      title: 'راهنمای سفر به هلیله بوشهر: کجاست، چه کنیم، کی برویم',
      excerpt:
        'هلیله کجاست و چرا ارزش یک سفر را دارد؟ راهنمای کوتاه سفر به روستای گردشگری هلیله بوشهر: موقعیت، بهترین زمان، برنامهٔ یک‌روزه و پیشنهاد اقامت.',
      date: '2026-11-09',
      lead: 'هلیله باستانی در ساحل خلیج فارس و نزدیک شهر بوشهر است. این راهنما به پرسش‌هایی جواب می‌دهد که پیش از سفر مطرح می‌شود.',
      sections: [
        {
          h: 'هلیله کجاست؟',
          p: [
            'هلیله یکی از قدیمی‌ترین محله‌های شبه‌جزیرهٔ بوشهر و جنوبی‌ترین محلهٔ این شهر است. بیش از چهار کیلومتر نوار ساحلی از پارک آفتاب تا پارک لیان دارد و جمعیتش حدود ۲۹۰۰ نفر است. لغت‌نامهٔ دهخدا هم هلیله را در ۱۶ کیلومتری جنوب‌خاوری بوشهر معرفی می‌کند.',
          ],
        },
        {
          h: 'چرا هلیله؟',
          p: [
            'هلیله برای کسی که می‌خواهد دریا را بدون شلوغی ببیند و با مردم محل آشنا شود جای خوبی است. ساحل، سفرهٔ جنوبی، کار دست هنرمندان محلی و تجربه‌هایی مثل قایق‌سواری، ماهی‌گیری و تور نجوم در یک مسیر کنار هم‌اند.',
            `فهرست کامل را در صفحهٔ <<تجربه‌ها|${S}/experiences/>> ببینید.`,
          ],
        },
        {
          h: 'بهترین زمان سفر',
          p: [
            'از پاییز تا بهار هوای بوشهر برای گردش مناسب‌تر است و صبح و عصر برای پیاده‌روی کنار دریا بهترین ساعت‌اند. تابستان گرم و شرجی است، ولی تجربه‌های خاص خودش را دارد که فقط همان فصل می‌شود دید.',
          ],
        },
        {
          h: 'برنامهٔ پیشنهادی',
          p: [
            `اگر یک روز وقت دارید، برنامهٔ <<یک روز در هلیله|${S}/one-day-trip-helileh-2/>> را دنبال کنید: صبح ساحل، ظهر غذای محلی و عصر کارگاه هنرمندان و غروب دریا.`,
          ],
        },
        {
          h: 'اقامت و هماهنگی',
          p: [
            `اقامتگاه‌های محلی را در صفحهٔ <<اقامتگاه‌ها|${S}/accommodations/>> ببینید. برای ظرفیت و هماهنگی بازدید با مدیریت تماس بگیرید: <<09176443247|tel:+989176443247>> یا به <<info@heleyleh-bu.ir|mailto:info@heleyleh-bu.ir>> ایمیل بزنید.`,
          ],
        },
      ],
    },
    {
      id: 'experiences-guide',
      slug: 'helileh-experiences-guide',
      title: 'تجربه‌های هلیله: از قایق‌سواری تا تور نجوم',
      excerpt:
        'در هلیله چه می‌شود کرد؟ قایق‌سواری، ماهی‌گیری، پدل‌بوردینگ، ورزش‌های ساحلی، بافت‌گردی، موزهٔ نجوم و دریانوردی و تور نجوم؛ هرکدام با یک هماهنگ‌کنندهٔ محلی.',
      date: '2026-11-07',
      lead: 'تجربه‌های هلیله را مردم خود هلیله هماهنگ می‌کنند. این نوشته فهرست کوتاهی از آنهاست.',
      sections: [
        {
          h: 'روی آب',
          p: [
            'قایق‌سواری ساحلی، ماهی‌گیری با صیادان محلی و پدل‌بوردینگ سه راه برای دیدن هلیله از سمت دریا هستند.',
          ],
        },
        {
          h: 'ورزش کنار ساحل',
          p: [
            'تنیس ساحلی، والیبال ساحلی، فوتبال ساحلی و تنیس خاکی در هلیله برنامه دارند. پارک ساحلی هم بدون نیاز به هماهنگی برای همه باز است.',
          ],
        },
        {
          h: 'تاریخ و آسمان',
          p: [
            'بافت‌گردی در محله‌های قدیمی، موزهٔ نجوم و دریانوردی، ساعت‌های آفتابی و تور نجوم برای کسانی است که دربارهٔ آسمان و دریانوردی کنجکاوند. شب‌های خلیج فارس برای تماشای ستاره‌ها فرصت خوبی‌اند.',
          ],
        },
        {
          h: 'چطور هماهنگ کنیم؟',
          p: [
            `برای هر تجربه نام و شمارهٔ هماهنگ‌کننده در صفحهٔ <<تجربه‌ها|${S}/experiences/>> آمده است. پیش از رفتن تماس بگیرید تا زمان مناسب را با هم بگذارید.`,
          ],
        },
      ],
    },
    {
      id: 'publisher-books',
      slug: 'helileh-publishing-books',
      title: 'نشر هلیله و کتاب‌هایی از دل جنوب',
      excerpt:
        'نشر هلیله به مدیریت مهدی انصاری کتاب‌هایی در رمان، داستان کوتاه، سینما و تئاتر، ادب فارسی و ماهیان خلیج فارس منتشر کرده است. فهرست و راه سفارش.',
      date: '2026-11-05',
      lead: 'در صفحهٔ «کتابخانه» سایت، کتاب‌های نشر هلیله روی یک قفسهٔ چوبی چیده شده‌اند و می‌توان هرکدام را روی میز تحریر باز کرد.',
      sections: [
        {
          h: 'کتاب‌ها',
          p: [
            'رمان: «آواز دانه‌ها» و «قلعه» از داریوش محمدزاده‌فرد، که قلعه دربارهٔ مردمان قلعهٔ برازجان است.',
            'داستان: «نمکو دمبو» از حمید آتشچی با فضای جنوب و بوشهر، و «چند داستان ساده از روزگار کسانی که نمی‌خندند» از محسن فردین.',
            'سینما و تئاتر: «مدیر صحنه در فیلم‌سازی» از احمد حیاوی دربارهٔ کار مدیر صحنه.',
            'خلیج فارس: «شیلی یو، شناخت ماهیان خلیج فارس» از اکبر شایان‌خواه.',
            'ادب فارسی: دو جلد «از هرات تا اجمیر و طوس» از احمدغنی خسروی. کتاب‌های «کاپیتان تیم فوتبال ما» (نصرالله احمدی) و «هرچه دیدی هیچی مگو» (سید عبدالله بلادی) هم در فهرست‌اند.',
          ],
        },
        {
          h: 'سفارش',
          p: [
            `کتاب‌ها را در صفحهٔ <<کتابخانه|${S}/blog/>> ببینید. برای سفارش مستقیم از ناشر با شمارهٔ <<09171731776|tel:+989171731776>> تماس بگیرید. نشانی نشر: بوشهر، روستای گردشگری هلیله.`,
          ],
        },
      ],
    },
    {
      id: 'calendar-guide',
      slug: 'helileh-calendar-festivals',
      title: 'تقویم هلیله: جشن‌های ایران باستان و بادهای محلی',
      excerpt:
        'نوروز، سیزده‌به‌در، فروردینگان و جشن‌های دیگر ایران باستان کنار بادهای محلی خلیج فارس در تقویم هلیله آمده‌اند. ببینید کدام روز برای سفر مناسب است.',
      date: '2026-11-03',
      lead: 'تقویم هلیله جشن‌های ایران باستان و بادهای محلی را به تقویم خورشیدی می‌آورد تا سفر را بهتر برنامه‌ریزی کنید.',
      sections: [
        {
          h: 'چرا تقویم؟',
          p: [
            'هلیله دریاست و دریا با باد زندگی می‌کند. بادهای محلی خلیج فارس هم برای صیادان و هم برای مسافران اهمیت دارند. جشن‌های ایران باستان هم فرصتی برای دیدن آیین‌ها هستند.',
          ],
        },
        {
          h: 'چند نمونه',
          p: [
            'نوروز، آغاز سال نو و نخستین روز بهار است. سیزده‌به‌در روز رفتن به دامن طبیعت است و ساحل هلیله برای آن مناسب است. فروردینگان روز یادبود درگذشتگان و فروهرهاست.',
          ],
        },
        {
          h: 'همهٔ تاریخ‌ها',
          p: [
            `فهرست کامل را در <<تقویم رویدادها|${S}/events/>> ببینید. تاریخ‌ها بر پایهٔ تقویم خورشیدی (جلالی) آمده‌اند.`,
          ],
        },
      ],
    },
  ],
  en: [
    {
      id: 'travel-guide',
      slug: 'helileh-bushehr-travel-guide-en',
      title: 'Helileh, Bushehr travel guide: where, what to do, when to go',
      excerpt:
        'Where is Helileh and why visit? A short guide to the tourist village of Helileh in Bushehr: location, best season, a one-day plan and where to stay.',
      date: '2026-11-09',
      lead: 'Helileh is on the Persian Gulf coast, close to the city of Bushehr. This guide answers the questions travellers usually ask first.',
      sections: [
        {
          h: 'Where is Helileh?',
          p: [
            'Helileh is one of the oldest neighbourhoods of the Bushehr peninsula and the southernmost one of the city. It has more than four kilometres of shoreline, from Aftab Park to Layan Park, and about 2,900 residents. The Dehkhoda dictionary places it 16 km south-east of Bushehr.',
          ],
        },
        {
          h: 'Why Helileh?',
          p: [
            'It suits anyone who wants the sea without crowds and a chance to meet local people. The beach, southern food, local crafts and activities like boat rides, fishing and an astronomy tour are all along one route.',
            `See the full list on the <<things to do|${S}/en/things-to-do/>> page.`,
          ],
        },
        {
          h: 'Best time to visit',
          p: [
            'From autumn to spring the weather in Bushehr is better for walking, and mornings and late afternoons are the best hours by the sea. Summer is hot and humid, but it has experiences you can only see in that season.',
          ],
        },
        {
          h: 'Stay and contact',
          p: [
            `Local stays are on the <<stay|${S}/en/stay/>> page. For capacity and visit arrangements call <<+98 917 644 3247|tel:+989176443247>> or write to <<info@heleyleh-bu.ir|mailto:info@heleyleh-bu.ir>>.`,
          ],
        },
      ],
    },
    {
      id: 'experiences-guide',
      slug: 'helileh-experiences-guide-en',
      title: 'Things to do in Helileh: from boat rides to astronomy tours',
      excerpt:
        'Boat rides, fishing, paddle boarding, beach sports, an old-quarter walk, the Astronomy & Seafaring Museum and astronomy tours: each arranged by a local coordinator.',
      date: '2026-11-07',
      lead: 'The people of Helileh coordinate its experiences themselves. Here is a short list.',
      sections: [
        {
          h: 'On the water',
          p: ['A coastal boat ride, fishing with local fishermen and paddle boarding are three ways to see Helileh from the sea.'],
        },
        {
          h: 'Sport by the beach',
          p: ['Beach tennis, beach volleyball, beach football and clay-court tennis are on offer. The beach park is open to everyone.'],
        },
        {
          h: 'History and sky',
          p: ['An old-quarter walk, the Astronomy & Seafaring Museum, sundials and an astronomy tour are for the curious. Persian Gulf nights are good for stargazing.'],
        },
        {
          h: 'How to arrange it',
          p: [`Each experience lists its coordinator on the <<things to do|${S}/en/things-to-do/>> page. Call ahead to agree a time.`],
        },
      ],
    },
    {
      id: 'publisher-books',
      slug: 'helileh-publishing-books-en',
      title: 'Helileh Publishing: books from the heart of the south',
      excerpt:
        'Helileh Publishing, managed by Mehdi Ansari, has published novels, short stories, books on film and theatre, Persian literature and a guide to Persian Gulf fish.',
      date: '2026-11-05',
      lead: 'On the Library page the publisher’s books stand on a wooden shelf, and each can be opened on a writing desk.',
      sections: [
        {
          h: 'The books',
          p: [
            'Novels: “Avaz-e Daneha” and “Ghale” by Dariush Mohammadzadehfard.',
            'Stories: “Namko Dambo” by Hamid Atashchi, set in the south, and a short-story collection by Mohsen Fardin.',
            'Film and theatre: “Stage Manager in Filmmaking” by Ahmad Hayavi.',
            'The Gulf: “Shili Yu: Identifying Persian Gulf Fish” by Akbar Shayankhah.',
            'Persian literature: the two volumes of “From Herat to Ajmer and Tus” by Ahmadghani Khosravi.',
          ],
        },
        {
          h: 'How to order',
          p: [`See the books on the <<Library|${S}/en/travel-blog/>> page. To order directly from the publisher call <<+98 917 173 1776|tel:+989171731776>>. Address: Helileh tourist village, Bushehr.`],
        },
      ],
    },
    {
      id: 'calendar-guide',
      slug: 'helileh-calendar-festivals-en',
      title: 'Helileh calendar: ancient Iranian festivals and local winds',
      excerpt:
        'Nowruz, Sizdah Bedar, Farvardingan and other festivals of ancient Iran, along with local Persian Gulf winds, are in the Helileh calendar. See which day suits your trip.',
      date: '2026-11-03',
      lead: 'The Helileh calendar lists ancient Iranian festivals and local winds by solar date so you can plan your visit.',
      sections: [
        {
          h: 'Why a calendar?',
          p: ['Helileh lives by the sea, and the sea lives by the wind. Local winds matter to fishermen and travellers alike, and ancient festivals are a chance to see old customs.'],
        },
        {
          h: 'A few examples',
          p: ['Nowruz is the new year and first day of spring. Sizdah Bedar is the day to head out into nature, and Helileh’s shore suits it. Farvardingan is a day to remember the departed.'],
        },
        {
          h: 'All the dates',
          p: [`See the full list in the <<events calendar|${S}/en/events-calendar/>>. Dates follow the Jalali solar calendar.`],
        },
      ],
    },
  ],
  ru: [
    {
      id: 'travel-guide',
      slug: 'helileh-bushehr-travel-guide-ru',
      title: 'Путеводитель по Хелиле, Бушер: где это, что делать, когда ехать',
      excerpt:
        'Где находится Хелиле и зачем сюда ехать? Короткий путеводитель по туристической деревне Хелиле в Бушере: расположение, лучшее время, план на день и жильё.',
      date: '2026-11-09',
      lead: 'Хелиле находится на берегу Персидского залива, недалеко от города Бушер. Этот путеводитель отвечает на вопросы, которые обычно задают перед поездкой.',
      sections: [
        {
          h: 'Где находится Хелиле?',
          p: [
            'Хелиле — один из старейших кварталов полуострова Бушер и самый южный квартал города. Здесь более четырёх километров побережья от парка Афтаб до парка Лиян и около 2900 жителей. Словарь Дехходы помещает его в 16 км к юго-востоку от Бушера.',
          ],
        },
        {
          h: 'Почему Хелиле?',
          p: [
            'Это место для тех, кто хочет увидеть море без толпы и познакомиться с местными жителями. Пляж, южная кухня, местные ремёсла, прогулки на лодке, рыбалка и астрономический тур находятся рядом.',
            `Полный список — на странице <<Впечатления|${S}/ru/vpechatleniya/>>.`,
          ],
        },
        {
          h: 'Лучшее время для поездки',
          p: [
            'С осени до весны погода в Бушере удобнее для прогулок, а лучшее время у моря — утро и вечер. Лето жаркое и влажное, но у него есть свои впечатления, доступные только в этот сезон.',
          ],
        },
        {
          h: 'Жильё и связь',
          p: [
            `Местное жильё — на странице <<Проживание|${S}/ru/prozhivanie/>>. Чтобы уточнить места и договориться о визите, позвоните <<+98 917 644 3247|tel:+989176443247>> или напишите на <<info@heleyleh-bu.ir|mailto:info@heleyleh-bu.ir>>.`,
          ],
        },
      ],
    },
    {
      id: 'experiences-guide',
      slug: 'helileh-experiences-guide-ru',
      title: 'Впечатления в Хелиле: от прогулок на лодке до астрономического тура',
      excerpt:
        'Прогулки на лодке, рыбалка, падл-бординг, пляжный спорт, прогулка по старому кварталу, Музей астрономии и мореходства и астротур: у каждого есть местный координатор.',
      date: '2026-11-07',
      lead: 'Впечатления в Хелиле организуют сами жители. Вот короткий список.',
      sections: [
        {
          h: 'На воде',
          p: ['Прогулка на лодке вдоль берега, рыбалка с местными рыбаками и падл-бординг — три способа увидеть Хелиле со стороны моря.'],
        },
        {
          h: 'Спорт у пляжа',
          p: ['Пляжный теннис, пляжный волейбол, пляжный футбол и теннис на грунте. Пляжный парк открыт для всех.'],
        },
        {
          h: 'История и небо',
          p: ['Прогулка по старому кварталу, Музей астрономии и мореходства, солнечные часы и астротур — для любознательных. Ночи на Персидском заливе хороши для наблюдения за звёздами.'],
        },
        {
          h: 'Как договориться',
          p: [`Для каждого впечатления на странице <<Впечатления|${S}/ru/vpechatleniya/>> указан координатор. Позвоните заранее и согласуйте время.`],
        },
      ],
    },
    {
      id: 'publisher-books',
      slug: 'helileh-publishing-books-ru',
      title: 'Издательство «Хелиле»: книги из сердца юга',
      excerpt:
        'Издательство «Хелиле» под руководством Мехди Ансари выпустило романы, рассказы, книги о кино и театре, персидскую литературу и справочник по рыбам Персидского залива.',
      date: '2026-11-05',
      lead: 'На странице «Библиотека» книги издательства стоят на деревянной полке, и каждую можно открыть на письменном столе.',
      sections: [
        {
          h: 'Книги',
          p: [
            'Романы: «Аваз-е данеха» и «Гале» Дарюша Мохаммадзаде-Фарда.',
            'Рассказы: «Намко Дамбо» Хамида Атешчи об южном Бушере и сборник рассказов Мохсена Фердина.',
            'Кино и театр: «Помощник режиссёра в кино» Ахмада Хаяви.',
            'Персидский залив: «Шили Ю: определитель рыб Персидского залива» Акбара Шаянхаха.',
            'Персидская литература: два тома «От Герата до Аджмера и Туса» Ахмадгани Хосрави.',
          ],
        },
        {
          h: 'Как заказать',
          p: [`Книги — на странице <<Библиотека|${S}/ru/blog-helile/>>. Чтобы заказать напрямую у издательства, позвоните <<+98 917 173 1776|tel:+989171731776>>. Адрес: туристическая деревня Хелиле, Бушер.`],
        },
      ],
    },
    {
      id: 'calendar-guide',
      slug: 'helileh-calendar-festivals-ru',
      title: 'Календарь Хелиле: праздники древнего Ирана и местные ветры',
      excerpt:
        'Новруз, Сиздах-бедар, Фарвардинган и другие праздники древнего Ирана и местные ветры Персидского залива есть в календаре Хелиле. Выберите день для поездки.',
      date: '2026-11-03',
      lead: 'Календарь Хелиле собирает праздники древнего Ирана и местные ветры по солнечному календарю, чтобы вы могли спланировать поездку.',
      sections: [
        {
          h: 'Зачем календарь?',
          p: ['Хелиле живёт морем, а море живёт ветром. Местные ветры важны и рыбакам, и путешественникам, а древние праздники — повод увидеть старые обычаи.'],
        },
        {
          h: 'Несколько примеров',
          p: ['Новруз — новый год и первый день весны. Сиздах-бедар — день выезда на природу, и берег Хелиле для него подходит. Фарвардинган — день памяти ушедших.'],
        },
        {
          h: 'Все даты',
          p: [`Полный список — в <<календаре событий|${S}/ru/sobytiya/>>. Даты даны по солнечному календарю Джалали.`],
        },
      ],
    },
  ],
};
