/*
 ---

 name: Locale.fi-FI.Date

 description: Date messages for Finnish.

 license: MIT-style license

 authors:
 - ksel

 requires:
 - /Locale

 provides: [Locale.fi-FI.Date]

 ...
 */

Locale.define('fi', 'Date', {

	// NOTE: months and days are not capitalized in finnish
	months: ['tammikuu', 'helmikuu', 'maaliskuu', 'huhtikuu', 'toukokuu', 'kesäkuu', 'heinäkuu', 'elokuu', 'syyskuu', 'lokakuu', 'marraskuu', 'joulukuu'],

	// these abbreviations are really not much used in finnish because they obviously won't abbreviate very much. ;)
	// NOTE: sometimes one can see forms such as "tammi", "helmi", etc. but that is not proper finnish.
	months_abbr: ['tammik.', 'helmik.', 'maalisk.', 'huhtik.', 'toukok.', 'kesäk.', 'heinäk.', 'elok.', 'syysk.', 'lokak.', 'marrask.', 'jouluk.'],

	days: ['sunnuntai', 'maanantai', 'tiistai', 'keskiviikko', 'torstai', 'perjantai', 'lauantai'],
	days_abbr: ['su', 'ma', 'ti', 'ke', 'to', 'pe', 'la'],

	// Culture's date order: DD/MM/YYYY
	dateOrder: ['date', 'month', 'year'],
	shortDate: '%d.%m.%Y',
	shortTime: '%H:%M',
	AM: 'AM',
	PM: 'PM',
	firstDayOfWeek: 1,

	// Date.Extras
	ordinal: '.',

	lessThanMinuteAgo: 'vajaa minuutti sitten',
	minuteAgo: 'noin minuutti sitten',
	minutesAgo: '{delta} minuuttia sitten',
	hourAgo: 'noin tunti sitten',
	hoursAgo: 'noin {delta} tuntia sitten',
	dayAgo: 'päivä sitten',
	daysAgo: '{delta} päivää sitten',
	weekAgo: 'viikko sitten',
	weeksAgo: '{delta} viikkoa sitten',
	monthAgo: 'kuukausi sitten',
	monthsAgo: '{delta} kuukautta sitten',
	yearAgo: 'vuosi sitten',
	yearsAgo: '{delta} vuotta sitten',

	lessThanMinuteUntil: 'vajaan minuutin kuluttua',
	minuteUntil: 'noin minuutin kuluttua',
	minutesUntil: '{delta} minuutin kuluttua',
	hourUntil: 'noin tunnin kuluttua',
	hoursUntil: 'noin {delta} tunnin kuluttua',
	dayUntil: 'päivän kuluttua',
	daysUntil: '{delta} päivän kuluttua',
	weekUntil: 'viikon kuluttua',
	weeksUntil: '{delta} viikon kuluttua',
	monthUntil: 'kuukauden kuluttua',
	monthsUntil: '{delta} kuukauden kuluttua',
	yearUntil: 'vuoden kuluttua',
	yearsUntil: '{delta} vuoden kuluttua'

});


/*
 ---

 name: Locale.fi-FI.Form.Validator

 description: Form Validator messages for Finnish.

 license: MIT-style license

 authors:
 - ksel

 requires:
 - /Locale

 provides: [Locale.fi-FI.Form.Validator]

 ...
 */

Locale.define('fi', 'FormValidator', {

	required: 'Tämä kenttä on pakollinen.',
	minLength: 'Ole hyvä ja anna vähintään {minLength} merkkiä (annoit {length} merkkiä).',
	maxLength: 'Älä anna enempää kuin {maxLength} merkkiä (annoit {length} merkkiä).',
	integer: 'Ole hyvä ja anna kokonaisluku. Luvut, joissa on desimaaleja (esim. 1.25) eivät ole sallittuja.',
	numeric: 'Anna tähän kenttään lukuarvo (kuten "1" tai "1.1" tai "-1" tai "-1.1").',
	digits: 'Käytä pelkästään numeroita ja välimerkkejä tässä kentässä (syötteet, kuten esim. puhelinnumero, jossa on väliviivoja, pilkkuja tai pisteitä, kelpaa).',
	alpha: 'Anna tähän kenttään vain kirjaimia (a-z). Välilyönnit tai muut merkit eivät ole sallittuja.',
	alphanum: 'Anna tähän kenttään vain kirjaimia (a-z) tai numeroita (0-9). Välilyönnit tai muut merkit eivät ole sallittuja.',
	dateSuchAs: 'Ole hyvä ja anna kelvollinen päivmäärä, kuten esimerkiksi {date}',
	dateInFormatMDY: 'Ole hyvä ja anna kelvollinen päivämäärä muodossa pp/kk/vvvv (kuten "12/31/1999")',
	email: 'Ole hyvä ja anna kelvollinen sähköpostiosoite (kuten esimerkiksi "matti@meikalainen.com").',
	url: 'Ole hyvä ja anna kelvollinen URL, kuten esimerkiksi http://www.example.com.',
	currencyDollar: 'Ole hyvä ja anna kelvollinen eurosumma (kuten esimerkiksi 100,00 EUR) .',
	oneRequired: 'Ole hyvä ja syötä jotakin ainakin johonkin näistä kentistä.',
	errorPrefix: 'Virhe: ',
	warningPrefix: 'Varoitus: ',

	// Form.Validator.Extras
	noSpace: 'Tässä syötteessä ei voi olla välilyöntejä',
	reqChkByNode: 'Ei valintoja.',
	requiredChk: 'Tämä kenttä on pakollinen.',
	reqChkByName: 'Ole hyvä ja valitse {label}.',
	match: 'Tämän kentän tulee vastata kenttää {matchName}',
	startDate: 'alkupäivämäärä',
	endDate: 'loppupäivämäärä',
	currendDate: 'nykyinen päivämäärä',
	afterDate: 'Päivämäärän tulisi olla sama tai myöhäisempi ajankohta kuin {label}.',
	beforeDate: 'Päivämäärän tulisi olla sama tai aikaisempi ajankohta kuin {label}.',
	startMonth: 'Ole hyvä ja valitse aloituskuukausi',
	sameMonth: 'Näiden kahden päivämäärän tulee olla saman kuun sisällä -- sinun pitää muuttaa jompaa kumpaa.',
	creditcard: 'Annettu luottokortin numero ei kelpaa. Ole hyvä ja tarkista numero sekä yritä uudelleen. {length} numeroa syötetty.'

});


/*
 ---

 name: Locale.fi-FI.Number

 description: Finnish number messages

 license: MIT-style license

 authors:
 - ksel

 requires:
 - /Locale
 - /Locale.EU.Number

 provides: [Locale.fi-FI.Number]

 ...
 */

Locale.define('fi', 'Number', {

	group: ' ' // grouped by space

}).inherit('EU', 'Number');
