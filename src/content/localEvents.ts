import type { Lang } from './types';
import { applyOverrides } from '@/lib/overrides';

/**
 * رویدادهای هلیله (بالای تقویم باستانی).
 * برای عکس واقعی: فایل را در public/events/ بگذارید و نامش را در photo بنویسید؛ تا آن زمان تصویر گرافیکی نمایش داده می‌شود.
 */
export type EventArtName = 'sport' | 'grigsho' | 'barat' | 'kolun' | 'domad' | 'theater';

interface Tx {
  title: string;
  desc: string;
  date: string;
  time: string;
  place: string;
}

export interface LocalEvent {
  id: string;
  art: EventArtName;
  /** رویداد با تقویم قمری (تاریخ خورشیدی‌اش هر سال جابه‌جا می‌شود) */
  lunar?: boolean;
  /** بازهٔ چندروزه یا ماهانه */
  span?: boolean;
  photo?: string;
  /** شعار و متن کامل (اختیاری) */
  slogan?: Record<Lang, string>;
  full?: Record<Lang, string[]>;
  /** نویسندهٔ متن */
  author?: Record<Lang, string>;
  t: Record<Lang, Tx>;
}

export const localEvents: LocalEvent[] = [
  {
    id: 'sport',
    art: 'sport',
    span: true,
    t: {
      fa: { title: 'رویداد ورزشی اردیبهشت', desc: 'برنامهٔ ورزشی یک‌ماهه در زمین چمن ساحلی هلیله؛ غروب‌ها به ساحل بیایید.', date: '۱ تا ۳۰ اردیبهشت', time: 'غروب', place: 'زمین چمن ساحلی' },
      en: { title: 'Ordibehesht sports event', desc: 'A month of sport on Helileh\'s beach grass pitch; come to the shore at sunset.', date: '1–30 Ordibehesht', time: 'Sunset', place: 'Beach grass pitch' },
      ru: { title: 'Спортивное событие ордибехешта', desc: 'Месяц спорта на пляжном травяном поле Хелиле; приходите на берег на закате.', date: '1–30 ордибехешта', time: 'Закат', place: 'Пляжное травяное поле' },
    },
  },
  {
    id: 'grigsho',
    art: 'grigsho',
    lunar: true,
    slogan: { fa: 'گریگشو؛ گره‌گشای دل‌ها', en: 'Gerigshow: the untier of hearts\' knots', ru: 'Григ-шо — тот, кто развязывает узлы сердец' },
    full: {
      fa: [
        'گریگشو آیینی سنتی، مذهبی و مردمی در محله تاریخی هلیله بندر بوشهر است که بیش از ۹۰۰ سال هر ساله در شب پانزدهم ماه مبارک رمضان، همزمان با میلاد امام حسن مجتبی (ع)، برگزار می‌شود. «گریگشو» در فرهنگ مردم این منطقه به معنای «گره‌گشا» و «حاجت‌روا» است و یکی از جلوه‌های ارزشمند فرهنگ شفاهی و میراث ناملموس بوشهر به شمار می‌رود.',
        'در این آیین کودکان و نوجوانان با کیسه‌هایی در دست به کوچه‌های محله می‌روند و در خانه‌ها را می‌زنند. اهالی که از پیش تنقلات و خوراکی آماده کرده‌اند، با روی گشاده از آنان استقبال می‌کنند و هدیه‌ای در کیسه‌هایشان می‌گذارند. در گریگشو مرز میان «مهمان» و «میزبان» کمرنگ می‌شود و همه اعضای جامعه، فارغ از سن، جایگاه و باور، در فضایی سرشار از شادی، همدلی و بخشندگی کنار یکدیگر قرار می‌گیرند.',
        'این آیین فقط یک مراسم مذهبی نیست؛ رویدادی عمیقاً اجتماعی و فرهنگی است که نسل به نسل منتقل شده و با پیوند دادن خانواده‌ها، کودکان، نوجوانان و اهالی محله، مفاهیمی چون سخاوت، مشارکت اجتماعی، همسایگی و همدلی را زنده نگه می‌دارد. به همین دلیل گریگشو یکی از جلوه‌های ماندگار هویت فرهنگی و اجتماعی ساکنان محله هلیله و بخشی ارزشمند از میراث زنده و مردمی بوشهر است.',
      ],
      en: [
        'Gerigshow is a traditional, religious, community-based ritual in the historic Helileh neighborhood of Bushehr. For more than 900 years it has been held annually on the 15th night of the holy month of Ramadan, coinciding with the birthday of Imam Hasan al-Mujtaba (peace be upon him). In the local culture, "Gerigshow" carries a meaning close to "the one who unties knots" and "the one who fulfills wishes," and it is a valued expression of Bushehr\'s oral culture and intangible heritage.',
        'Children and teenagers carry bags through the neighborhood\'s alleys and knock on doors. Residents, who have prepared snacks and treats in advance, welcome them warmly and place a gift in each bag. In Gerigshow the line between "guest" and "host" fades: people of every age, standing, and belief come together in an atmosphere of joy, empathy, and generosity.',
        'The ritual is more than a religious ceremony. It is a deeply social and cultural event, passed down through generations, that bonds families, children, teenagers, and neighbors and keeps alive values such as generosity, participation, neighborliness, and empathy. For this reason, Gerigshow is a lasting expression of the cultural and social identity of Helileh\'s residents and a valuable part of Bushehr\'s living, popular heritage.',
      ],
      ru: [
        'Григ-шо — традиционный, религиозный и народный обряд в историческом квартале Хелиле города Бушер. Более 900 лет он проводится ежегодно в пятнадцатую ночь священного месяца рамадан, совпадающую с днём рождения имама Хасана аль-Муджтабы (мир ему). На местном языке «григ-шо» близко по смыслу к «тот, кто развязывает узлы» и «исполняющий желания»; это ценное проявление устной культуры и нематериального наследия Бушера.',
        'Дети и подростки с мешочками в руках ходят по переулкам квартала и стучат в двери. Жители, заранее приготовившие сладости и угощения, радушно встречают их и кладут подарок в каждый мешочек. В Григ-шо стирается грань между «гостем» и «хозяином»: люди любого возраста, положения и убеждений оказываются рядом в атмосфере радости, сопереживания и щедрости.',
        'Это не только религиозная церемония, а глубоко общественное и культурное событие, передающееся из поколения в поколение, объединяющее семьи, детей, подростков и соседей и сохраняющее такие ценности, как щедрость, участие, добрососедство и сопереживание. Поэтому Григ-шо — стойкое выражение культурной и социальной идентичности жителей Хелиле и ценная часть живого народного наследия Бушера.',
      ],
    },
    author: { fa: 'وحید حسن احمدی', en: 'Vahid Hassan Ahmadi', ru: 'Вахид Хасан Ахмади' },
    t: {
      fa: { title: 'گریگشو', desc: 'آیینی بیش از ۹۰۰ ساله در هلیله؛ کودکان با کیسه‌هایی در دست در کوچه‌ها می‌گردند و اهالی با هدیه و خوراکی از آنان پذیرایی می‌کنند. نماد سخاوت و همدلی.', date: '۱۵ رمضان', time: 'غروب', place: 'کل محله هلیله' },
      en: { title: 'Gerigshow', desc: 'A ritual over 900 years old in Helileh: children walk the alleys with bags and residents welcome them with gifts and treats. A symbol of generosity and empathy.', date: '15 Ramadan', time: 'Sunset', place: 'Whole Helileh quarter' },
      ru: { title: 'Григ-шо', desc: 'Обряду более 900 лет: дети с мешочками ходят по переулкам, а жители угощают их сладостями и дарят подарки. Символ щедрости и сопереживания.', date: '15 рамазана', time: 'Закат', place: 'Весь квартал Хелиле' },
    },
  },
  {
    id: 'barat',
    art: 'barat',
    lunar: true,
    t: {
      fa: { title: 'نیمه برات', desc: 'نیمهٔ شعبان، شب برات و سالروز ولادت امام زمان (عج) نزد شیعیان؛ عصر ۱۵ شعبان در سراسر محلهٔ هلیله.', date: '۱۵ شعبان', time: 'عصر', place: 'کل محله هلیله' },
      en: { title: 'Nimeh Barat', desc: 'Mid-Sha\'ban, the Night of Bara\'at and, for Shia Muslims, the birth anniversary of the 12th Imam; in the afternoon of 15 Sha\'ban across the Helileh quarter.', date: '15 Sha\'ban', time: 'Afternoon', place: 'Whole Helileh quarter' },
      ru: { title: 'Ниме-барат', desc: 'Середина шабана — Ночь Бараат и день рождения двенадцатого имама у шиитов; после полудня 15 шабана во всём квартале Хелиле.', date: '15 шабана', time: 'После полудня', place: 'Весь квартал Хелиле' },
    },
  },
  {
    id: 'kolun',
    art: 'kolun',
    slogan: { fa: 'هر در، یک قاب؛ هر قاب، یک قصه', en: 'Every door a frame; every frame a story', ru: 'Каждая дверь — рама, каждая рама — история' },
    full: {
      fa: [
        'رویداد هنری «کلون» حرکتی مردمی و داوطلبانه است که هر سال در آذرماه در محله هلیله بندر بوشهر برگزار می‌شود. در این رویداد، هنرمندان هنرهای تجسمی از سراسر استان گرد هم می‌آیند و با رنگ و خلاقیت، درهای قدیمی و فرسوده را به قاب‌هایی برای خلق آثار هنری تبدیل می‌کنند.',
        'کلون تلاشی است برای زیباسازی محله، پاسداشت هویت و خاطره‌های بومی، تقویت مشارکت اجتماعی و جذب گردشگر؛ حرکتی که نشان می‌دهد با نگاهی هنرمندانه می‌توان به عناصر فراموش‌شده زندگی دوباره جان بخشید.',
      ],
      en: [
        '"Kalun" is a grassroots, volunteer-driven art event held every year in the month of Azar (November–December) in the Helileh neighborhood of Bushehr. Visual artists from across the province come together to turn old, worn-out doors into frames for artworks, using color and creativity.',
        'Kalun aims to beautify the neighborhood, honor local identity and memories, strengthen social participation, and attract tourists. It shows how a creative eye can give new life to forgotten elements of everyday life.',
      ],
      ru: [
        'Художественное событие «Колун» — народная добровольческая инициатива, которая проходит каждый год в месяц азар (ноябрь–декабрь) в квартале Хелиле города Бушер. Художники-изобразители со всей провинции собираются и красками и фантазией превращают старые, обветшалые двери в рамы для произведений искусства.',
        '«Колун» стремится украсить квартал, сохранить местную идентичность и память, укрепить общественное участие и привлечь туристов. Эта инициатива показывает, что взглядом художника можно вернуть жизнь забытым элементам повседневности.',
      ],
    },
    author: { fa: 'وحید حسن احمدی', en: 'Vahid Hassan Ahmadi', ru: 'Вахид Хасан Ахмади' },
    t: {
      fa: { title: 'کلون', desc: 'حرکتی مردمی و داوطلبانه که در آن هنرمندان تجسمی استان، درهای قدیمی و فرسوده محله هلیله را با رنگ و خلاقیت به قاب آثار هنری تبدیل می‌کنند.', date: 'آذرماه', time: 'صبح تا عصر', place: 'بافت قدیم هلیله' },
      en: { title: 'Kalun', desc: 'A grassroots, volunteer-driven event where the province\'s visual artists turn the old, worn doors of Helileh into frames for art with colour and creativity.', date: 'Azar', time: 'Morning to afternoon', place: 'Helileh old quarter' },
      ru: { title: 'Колун', desc: 'Народная добровольческая акция: художники-изобразители провинции превращают старые, обветшалые двери квартала Хелиле в рамы для произведений искусства.', date: 'месяц азар', time: 'С утра до вечера', place: 'Старый квартал Хелиле' },
    },
  },
  {
    id: 'domad',
    art: 'domad',
    t: {
      fa: { title: 'دوماد سر رود', desc: 'عروسی قدیمی هلیله، عصر یک روز از بهمن‌ماه در میدان پریزاد.', date: 'بهمن‌ماه', time: 'عصر', place: 'میدان پریزاد هلیله' },
      en: { title: 'Domad Sar Rood', desc: 'An old Helileh wedding, held on an afternoon in Bahman at Parizad square.', date: 'Bahman', time: 'Afternoon', place: 'Parizad square, Helileh' },
      ru: { title: 'Домад-сар-руд', desc: 'Старинная свадьба Хелиле, проходит в один из дней бахмана после полудня на площади Паризад.', date: 'месяц бахман', time: 'После полудня', place: 'Площадь Паризад, Хелиле' },
    },
  },
  {
    id: 'theater',
    art: 'theater',
    span: true,
    slogan: { fa: 'نمایش در آغوش دریا', en: 'Theatre in the embrace of the sea', ru: 'Спектакль в объятиях моря' },
    full: {
      fa: [
        'جشنواره تئاتر ساحلی رویدادی هنری و فرهنگی است که هر سال در فصل زمستان، در ساحل محله هلیله بندر بوشهر برگزار می‌شود. این جشنواره با ارائه گونه‌ای متفاوت از تئاتر خیابانی، هنر نمایش را از سالن‌های رسمی به فضای باز و به دل جامعه می‌آورد.',
        'گروه‌ها و هنرمندانی از سراسر کشور در این رویداد گرد هم می‌آیند و آثاری با محوریت محیط زیست، صلح و مقاومت، فرهنگ بومی و بازآفرینی قصه‌های عامیانه محلی اجرا می‌کنند. جشنواره می‌کوشد میان هنر نمایش، مردم و فضای طبیعی ساحل ارتباطی مستقیم بسازد.',
        'آشنایی عموم مردم با هنر تئاتر، گسترش فرهنگ نمایشی، تقویت مشارکت اجتماعی و توجه به حفاظت از محیط زیست از مهم‌ترین اهداف این رویداد است. برگزاری جشنواره در ساحل هلیله با بهره‌گیری از ظرفیت‌های طبیعی و فرهنگی منطقه، زمینه‌ساز توسعه گردشگری دریامحور و معرفی ظرفیت‌های هنری و فرهنگی بوشهر به گردشگران نیز هست.',
        'جشنواره تئاتر ساحلی تلاشی است برای پیوند هنر، جامعه، طبیعت و گردشگری؛ رویدادی که در آن ساحل فقط محل اجرای نمایش نیست و خود بخشی از تجربه هنری و فرهنگی مخاطبان می‌شود.',
      ],
      en: [
        'The Coastal Theatre Festival is an annual arts and cultural event held every winter on the shore of the Helileh neighborhood in Bushehr. Offering a different kind of street theatre, it brings the performing arts out of formal halls and into open space, at the heart of the community.',
        'Troupes and artists from across Iran gather to stage works centered on the environment, peace and resistance, local culture, and reimagined folk tales. The festival seeks to create a direct connection between theatre, the public, and the natural setting of the beach.',
        'Introducing the public to theatre, spreading a culture of performance, strengthening social participation, and promoting environmental protection are among its main goals. Held on Helileh beach, the festival also draws on the region\'s natural and cultural assets to develop sea-oriented tourism and introduce visitors to Bushehr\'s artistic heritage.',
        'The festival is, in essence, an effort to link art, society, nature, and tourism: an event in which the beach is not merely a venue but becomes part of the audience\'s artistic and cultural experience.',
      ],
      ru: [
        'Фестиваль пляжного театра — ежегодное художественное и культурное событие, которое каждую зиму проходит на берегу в квартале Хелиле города Бушер. Это особый вид уличного театра: он выводит искусство сцены из официальных залов на открытый воздух, в самое сердце общины.',
        'Труппы и артисты со всего Ирана собираются здесь и показывают работы об окружающей среде, мире и сопротивлении, местной культуре и заново рассказанных народных сказках. Фестиваль стремится создать прямую связь между театром, людьми и природной средой пляжа.',
        'Среди главных целей — знакомство широкой публики с театром, развитие театральной культуры, укрепление общественного участия и забота об охране природы. Проведение фестиваля на пляже Хелиле опирается на природные и культурные возможности региона и способствует развитию морского туризма и знакомству гостей с художественным наследием Бушера.',
        'Фестиваль — это попытка соединить искусство, общество, природу и туризм: событие, в котором пляж — не просто площадка, а часть художественного и культурного опыта зрителей.',
      ],
    },
    author: { fa: 'وحید حسن احمدی', en: 'Vahid Hassan Ahmadi', ru: 'Вахид Хасан Ахмади' },
    t: {
      fa: { title: 'جشنواره تئاتر ساحلی', desc: 'تئاتر خیابانی در ساحل هلیله بوشهر با حضور گروه‌های سراسر کشور؛ آثاری درباره محیط زیست، صلح، فرهنگ بومی و قصه‌های محلی. ساحل، خود بخشی از تجربه نمایش است.', date: '۲۵ تا ۲۹ بهمن', time: 'عصر', place: 'تیاتر ساحلی ایرج صغیری' },
      en: { title: 'Coastal Theatre Festival', desc: 'Street theatre on Helileh beach in Bushehr, with troupes from across the country; works about the environment, peace, local culture and folk tales. The beach is itself part of the show.', date: '25–29 Bahman', time: 'Afternoon', place: 'Iraj Soghayri beach theater' },
      ru: { title: 'Фестиваль пляжного театра', desc: 'Уличный театр на пляже Хелиле в Бушере с участием трупп со всей страны: постановки об экологии, мире, местной культуре и народных сказках. Сам пляж — часть спектакля.', date: '25–29 бахмана', time: 'После полудня', place: 'Пляжный театр Ираджа Согейри' },
    },
  },
];

export const localEventsUi: Record<
  Lang,
  { title: string; lead: string; cols: { event: string; date: string; time: string; place: string; entry: string }; free: string; organizer: string; community: string; lunar: string; photoSoon: string; source: string; sourceUrl: string; more: string; by: string }
> = {
  fa: { title: 'رویدادهای هلیله', lead: 'آیین‌ها و برنامه‌هایی که هر سال در هلیله برگزار می‌شود؛ همه رایگان و به همت مردم.', cols: { event: 'رویداد', date: 'تاریخ', time: 'ساعت', place: 'مکان', entry: 'ورودی' }, free: 'رایگان', organizer: 'برگزارکننده', community: 'مردمی', lunar: 'تاریخ قمری', photoSoon: 'عکس به‌زودی', source: 'دربارهٔ نیمهٔ شعبان', sourceUrl: 'https://en.wikipedia.org/wiki/Mid-Sha%27ban', more: 'متن کامل', by: 'متن' },
  en: { title: 'Helileh events', lead: 'Rituals and programmes held in Helileh every year, all free and run by the community.', cols: { event: 'Event', date: 'Date', time: 'Time', place: 'Place', entry: 'Entry' }, free: 'Free', organizer: 'Organiser', community: 'Community', lunar: 'Lunar date', photoSoon: 'Photo soon', source: 'About Mid-Sha\'ban', sourceUrl: 'https://en.wikipedia.org/wiki/Mid-Sha%27ban', more: 'Read the full text', by: 'Text' },
  ru: { title: 'События Хелиле', lead: 'Обряды и программы, которые проходят в Хелиле каждый год; всё бесплатно и силами жителей.', cols: { event: 'Событие', date: 'Дата', time: 'Время', place: 'Место', entry: 'Вход' }, free: 'Бесплатно', organizer: 'Организатор', community: 'Жители', lunar: 'Лунная дата', photoSoon: 'Фото скоро', source: 'О середине шабана', sourceUrl: 'https://en.wikipedia.org/wiki/Mid-Sha%27ban', more: 'Читать полностью', by: 'Текст' },
};
applyOverrides('localEvents', localEvents);
applyOverrides('localEventsUi', localEventsUi);
