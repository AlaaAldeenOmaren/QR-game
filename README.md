# QR-Game

QR-Game is een webapplicatie voor een schoolspel. Studenten scannen QR-codes, beantwoorden vragen en verzamelen punten. De organisator maakt spellen aan, beheert vragen, beoordeelt open antwoorden en bekijkt de resultaten.

De interface is in het Nederlands. Het project gebruikt Laravel, Blade, MySQL, CSS en JavaScript.

## Functies

### Voor studenten

- Een vraag openen door de QR-code met de telefooncamera te scannen.
- Deelnemen met een studentnummer. Nullen aan het begin blijven behouden.
- Meerkeuzevragen en open vragen beantwoorden.
- Eigen antwoorden, punten en feedback bekijken via **Voortgang**.
- De ranglijst bekijken, met de eigen positie en gedeelde plaatsen bij gelijke punten.
- Een opgeslagen antwoord terugzien bij het opnieuw openen van dezelfde vraag.
- Invoer op de vraagpagina behouden als verzenden mislukt, zodat opnieuw proberen mogelijk is.

### Voor de organisator

- Inloggen op het beheergedeelte.
- Eigen spellen bekijken via **Mijn spellen** en een spel aanmaken via **Nieuw spel**.
- Een spel kiezen met **Beheren**. Dashboard, vragen, beoordelingen en resultaten horen daarna bij dat spel.
- Vragen toevoegen en bewerken wanneer de spelstatus dit toestaat.
- Per vraag een QR-code bekijken en als SVG downloaden.
- Het spel starten, pauzeren, hervatten en stoppen.
- Open antwoorden beoordelen met punten en feedback.
- Resultaten bekijken en downloaden als CSV.

### Voor de beheerder

- Accounts van beheerders en organisatoren bekijken via **Accounts**.
- Nieuwe organisatoraccounts aanmaken via **Nieuw account**.
- Zelf spellen aanmaken en beheren, met dezelfde spelregels als een organisator.

Een organisator beheert alleen de eigen spellen. Een beheerder kan accounts aanmaken, maar krijgt daardoor geen toegang tot de spellen van andere organisatoren. Studenten gebruiken een studentnummer en hebben geen organisatoraccount nodig.

## Benodigdheden

- PHP met de extensies die Composer en Laravel nodig hebben, inclusief de MySQL-driver voor PDO.
- Composer en Git.
- Een draaiende MySQL-server.
- Een browser; voor de QR-test ook een telefoon met camera.

De lokale ontwikkelomgeving gebruikt **PHP 8.4.25**, **Laravel 13.30.1** en **Laravel Herd op Windows**. Gebruik voor het volgen van deze handleiding PHP 8.4.

In `composer.json` staan onder andere `laravel/framework: ^13.17` en `endroid/qr-code: 6.1`. Gebruik `composer install` om de versies uit `composer.lock` te installeren. Composer controleert ook de eisen van de andere pakketten.

De huidige QR-Game-pagina's gebruiken CSS en JavaScript rechtstreeks uit `public/`. Daarvoor hoeft geen Vite-server te draaien. Het algemene script `composer run dev` start ook Vite en een queue worker en heeft daarom extra Node/npm-afhankelijkheden.

## Installatie op een nieuw apparaat

Deze stappen zijn voor een **nieuwe lokale installatie met een lege database**. Voer ze niet opnieuw uit om een bestaande installatie met antwoorden en resultaten te vervangen.

### 1. Project ophalen

Open een terminal in de map waarin het project moet komen:

```powershell
git clone https://github.com/AlaaAldeenOmaren/QR-game.git
cd QR-game
composer install
```

### 2. Lokale instellingen maken

Kopieer in PowerShell het voorbeeldbestand als `.env` nog niet bestaat:

```powershell
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
```

Maak via je databaseprogramma een lege MySQL-database met de naam `qr_game`. Pas daarna deze waarden in `.env` aan:

```dotenv
APP_NAME="QR-Game"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qr_game
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Vul bij `DB_USERNAME` en `DB_PASSWORD` de gegevens van jouw lokale MySQL-server in. De waarden hierboven volgen het voorbeeldbestand; ze werken alleen als jouw MySQL-account daarmee overeenkomt.

Maak voor deze nieuwe installatie de applicatiesleutel en tabellen aan:

```powershell
php artisan key:generate
php artisan config:clear
php artisan migrate
```

Behoud bij een bestaande installatie de huidige `APP_KEY`. Bewaar `.env` buiten Git.

### 3. Eerste beheerder aanmaken

Voer na de migraties dit projectcommando uit. Vervang het voorbeeldadres door het e-mailadres dat je voor het account wilt gebruiken:

```powershell
php artisan qr-game:create-admin beheerder@example.com
```

Het commando vraagt om een naam, een wachtwoord van minimaal 12 tekens en een herhaling van dat wachtwoord. Het wachtwoord is tijdens het typen niet zichtbaar. Het wordt als hash opgeslagen. Er is geen vast standaardwachtwoord.

Bestaat het e-mailadres al? Dan vraagt het commando eerst of je dat bestaande account beheerder wilt maken. Bij bevestiging blijven het account-ID, het wachtwoord en de eigen spellen behouden. Is het account al beheerder, dan verandert er niets.

Dit commando maakt een account voor QR-Game aan. Het maakt geen e-mailpostvak en verstuurt geen e-mail. Nieuwe organisatoren maak je daarna via **Accounts** aan.

De installatie heeft geen demo-seeders nodig. Spellen en vragen worden via de website aangemaakt. Voer voor deze installatie geen `db:seed` uit.

### 4. Website starten

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Open [http://localhost:8000](http://localhost:8000). Laat de terminal open zolang je de website gebruikt.

Met een bestaande Herd-configuratie kan het project ook via [http://qr-game.test](http://qr-game.test) worden geopend. Gebruik binnen een test steeds hetzelfde adres: `qr-game.test`, `localhost` en het IP-adres hebben afzonderlijke browsersessies.

## Bestaande installatie bijwerken

Bewaar de bestaande `.env` en `APP_KEY` en maak eerst een back-up van de MySQL-database. Nadat de gewijzigde projectbestanden zijn opgehaald, installeer je de afhankelijkheden en voer je alleen de nog niet uitgevoerde migraties uit:

```powershell
composer install
php artisan config:clear
php artisan migrate
php artisan route:clear
php artisan view:clear
```

De migratie `2026_09_26_090000_add_is_admin_to_users_table.php` voegt `is_admin` toe aan `users`, standaard met de waarde `false`. Bestaande accounts blijven organisator. Hun wachtwoorden, spellen en antwoorden worden door deze migratie niet vervangen. Gebruik het commando uit stap 3 om een gekozen account beheerdersrechten te geven.

Gebruik geen `migrate:fresh`, `migrate:refresh` of `migrate:reset` bij het bijwerken van de bestaande database. Die opdrachten kunnen bestaande gegevens verwijderen.

## Gebruik

### Beheerder: een organisatoraccount aanmaken

1. Log in via `/beheer/inloggen` met het beheerdersaccount.
2. Open **Accounts** en kies **Nieuw account**.
3. Vul de naam, een uniek e-mailadres, een wachtwoord van minimaal 12 tekens en de wachtwoordbevestiging in.
4. Sla het account op. Het nieuwe account krijgt de rol **Organisator**.
5. Log met dat nieuwe account in om eigen spellen aan te maken.

Een nieuw account begint met een lege lijst spellen. Bestaande spellen blijven bij hun oorspronkelijke organisator. Via het formulier kun je geen extra beheerders aanmaken. Dat kan alleen met het projectcommando uit stap 3.

De pagina's voor accounts zijn beschermd met inloggen en `EnsureAccountAdministrator`. Een gewone organisator hoort bij een rechtstreeks bezoek aan `/beheer/accounts` een 403-melding te krijgen.

### Organisator

1. Open `/beheer/inloggen` en log in met je organisatoraccount.
2. Open **Mijn spellen**. Kies **Nieuw spel**, vul een naam in en sla het spel op. Het dashboard van het nieuwe spel opent automatisch.
3. Voeg via **Vraag toevoegen** of **Vragen** minstens Ã©Ã©n vraag toe. Een nieuw spel zonder vragen kan nog niet starten.
4. Open de bewerkpagina van een vraag om de QR-code te bekijken of te downloaden.
5. Start het spel via het **Dashboard**.
6. Beoordeel open antwoorden via **Nakijken**. Meerkeuzevragen krijgen automatisch punten.
7. Bekijk **Resultaten** en kies **Download CSV** om de uitslag te exporteren.

Wil je een ander spel beheren? Open **Mijn spellen** en klik bij dat spel op **Beheren**. Controleer de spelnaam in het beheergedeelte. De pagina's voor vragen, beoordelingen en resultaten volgen deze keuze. Een ander spel kiezen verandert de spelstatus niet.

Vragen toevoegen of bewerken kan bij een nog niet gestart of gepauzeerd spel. Een vraag waarop al antwoorden zijn ingediend, kan niet meer worden bewerkt.

### Student

1. Scan een QR-code van het spel met de camera van je telefoon.
2. Open de link. Vul bij de eerste deelname je studentnummer in.
3. Beantwoord de vraag wanneer het spel actief is.
4. Bekijk de bevestiging. Bij een open vraag kunnen de punten nog op beoordeling wachten.
5. Gebruik **Voortgang** voor je antwoorden en **Ranglijst** voor je positie.
6. Scan de volgende QR-code om verder te spelen.

Een nieuwe bezoeker ziet op de homepage uitleg over deelname. De knop **Hoe doe ik mee?** verwijst naar die uitleg. Deelname begint bij een QR-code van een vraag. Als er al een spel in de browsersessie bekend is, kan de homepage links naar dat spel tonen.

Opgeslagen antwoorden blijven in de database staan. Na verlies van de browsersessie kan een bestaande deelnemer via de voortgangspagina van hetzelfde spel het studentnummer opnieuw invullen. Dit is anders dan een nog niet verzonden antwoord: wacht op een bevestiging voordat je ervan uitgaat dat het is opgeslagen.

### Spelstatus

| Status | Betekenis voor de student |
| --- | --- |
| Nog niet gestart | Wachten tot de organisator het spel start. |
| Actief | Deelnemen en antwoorden versturen. |
| Gepauzeerd | Geen nieuwe antwoorden versturen; opgeslagen antwoorden en punten blijven bewaard. |
| Afgelopen | Geen nieuwe antwoorden versturen; bestaande voortgang en resultaten blijven beschikbaar. |

**Stoppen beÃ«indigt het spel.** Gebruik pauzeren als je later verder wilt spelen. De ranglijst kan nog veranderen zolang open antwoorden op beoordeling wachten.

## Testen op een telefoon via wifi

Verbind laptop en telefoon met hetzelfde lokale netwerk. Zoek met `ipconfig` het IPv4-adres van de laptop.

In de uitgevoerde test was dat `192.168.2.13`. Vervang dit voorbeeld door jouw huidige adres:

```powershell
php artisan serve --host=192.168.2.13 --port=8000
```

Open daarna [http://192.168.2.13:8000](http://192.168.2.13:8000) op de telefoon. Sta PHP indien nodig toe op het **privÃ©netwerk** in de Windows-firewall.

Open ook het beheergedeelte op de laptop via dit IP-adres. Ga naar `/beheer/vragen` en open de QR-code van een vraag. Controleer vÃ³Ã³r het scannen dat de link hetzelfde IP-adres en poortnummer gebruikt. Een QR-code met `qr-game.test` of `localhost` verwijst op de telefoon niet automatisch naar de laptop.

Deze lokale test gebruikt HTTP. De browser kan daarom waarschuwen bij het versturen van gegevens. Gebruik lokale testgegevens; voor gebruik via internet is HTTPS met een geldig certificaat nodig. De ontwikkelserver is bedoeld voor lokaal testen.

## Belangrijke pagina's

De paden hieronder komen achter het adres van de website. Vervang `{game}` door een bestaand spel-ID en `{qr_token}` door het token uit de QR-link.

| Pagina | Pad |
| --- | --- |
| Homepage | `/` |
| Inloggen organisator | `/beheer/inloggen` |
| Accounts, alleen voor beheerders | `/beheer/accounts` |
| Nieuw organisatoraccount, alleen voor beheerders | `/beheer/accounts/nieuw` |
| Eigen spellen | `/beheer/spellen` |
| Nieuw spel | `/beheer/spellen/nieuw` |
| Dashboard | `/beheer` |
| Vragen beheren | `/beheer/vragen` |
| Open antwoorden beoordelen | `/beheer/nakijken` |
| Resultaten en CSV-download | `/beheer/resultaten` |
| Vraag openen | `/spelen/vragen/{qr_token}` |
| Spelpagina | `/spelen/{game}` |
| Eigen voortgang | `/spelen/{game}/voortgang` |
| Ranglijst | `/spelen/{game}/ranglijst` |

## Bestanden

| Map of bestand | Inhoud |
| --- | --- |
| `app/Http/Controllers/` | Verwerking van verzoeken van studenten en organisatoren. |
| `app/Console/Commands/CreateAdministrator.php` | Eerste beheerder aanmaken of een bestaand account na bevestiging promoveren. |
| `app/Http/Middleware/EnsureAccountAdministrator.php` | Toegang tot accountbeheer controleren. |
| `app/Support/AccountRules.php` | Gedeelde validatieregels voor nieuwe accounts. |
| `app/Services/OrganizerGameContext.php` | Bepaalt het gekozen spel voor de organisator en controleert of het spel van deze organisator is. |
| `app/Models/` | Modellen voor onder andere spellen, vragen, deelnemers en antwoorden. |
| `resources/views/` | Blade-pagina's voor de website. |
| `resources/views/organizer/games/` | Overzicht van eigen spellen en formulier voor een nieuw spel. |
| `resources/views/organizer/accounts/` | Accountoverzicht en formulier voor een nieuwe organisator. |
| `resources/views/student/partials/nav.blade.php` | Navigatie voor de student. |
| `public/css/qr-game.css` | Vormgeving van QR-Game. |
| `public/js/student-answer.js` | Versturen van antwoorden en afhandeling van verzendproblemen. |
| `routes/web.php` | Webroutes. |
| `database/migrations/` | Opbouw van de database. |
| `database/seeders/DatabaseSeeder.php` | Standaard testgebruiker; maakt geen speelbaar spel aan. |
| `tests/Feature/OrganizerAccountsTest.php` | Automatische controles van accountbeheer en de nieuwe migratie. |
| `phpunit.accounts.xml` | Aparte testconfiguratie voor accountbeheer. |

## Automatische tests voor accountbeheer

De accounttests gebruiken een aparte SQLite-database in het geheugen. Daarvoor moet de PHP-extensie `pdo_sqlite` beschikbaar zijn. De tests zijn bedoeld om onder andere toegangsrechten, validatie, wachtwoordopslag en behoud van bestaande gegevens bij de migratie te controleren.

Voer vanuit de projectmap uit:

```powershell
php artisan config:clear
php artisan test --configuration=phpunit.accounts.xml
```

De testklasse bevat 10 tests. Zonder `pdo_sqlite` worden ze overgeslagen. Een overgeslagen test is geen geslaagde test. Bewaar de uitvoer als bewijs van de werkelijk uitgevoerde controles.

## Controles en aandachtspunten

Tijdens de ontwikkeling zijn onder andere antwoorden, handmatige beoordeling, ranglijst, CSV-export en foutpagina's in de browser gecontroleerd. Ook het opnieuw openen van een tabblad en het openen van een vraag via een QR-code op de telefoon zijn handmatig getest. Dit is geen bewijs dat alle mogelijke situaties of alle automatische tests zijn geslaagd.

Het aanmaken van een nieuw spel en het kiezen van een bestaand spel zijn ook handmatig gecontroleerd. Een nieuw spel begon zonder vragen en deelnemers. De startknop was uitgeschakeld totdat er een vraag was toegevoegd.

Het accountoverzicht is ook in de browser gecontroleerd: het beheerdersaccount en de organisatoren verschenen met hun rollen. Het bestaande demo-organisatoraccount had nog vier gekoppelde spellen.

Een volledige installatie op een aparte, lege database is nog niet vastgelegd. Ook is er nog geen opgeslagen uitvoer van de automatische accounttests. Controleer bij de installatieproef het aanmaken van de eerste beheerder, een organisator, een spel en vragen. Gebruik daarvoor een aparte database zodat de bestaande resultaten behouden blijven.

Praktische aandachtspunten:

- Het CSV-bestand gebruikt een puntkomma als scheidingsteken. Importeer studentnummers in Excel als **tekst** om nullen aan het begin te behouden.
- Een melding dat opslaan niet is bevestigd betekent dat de uitkomst onzeker is. Controleer de verbinding en probeer opnieuw; een bestaand antwoord hoort niet dubbel te worden toegevoegd.
- Een studentnummer is de toegang tot voortgang in dit prototype. Het is geen wachtwoord of sterke identiteitscontrole. Gebruik voor demonstraties fictieve gegevens.
- Voor publieke inzet moeten onder andere HTTPS, productie-instellingen en toegang tot studentgegevens worden beoordeeld.

## Documentatie van gebruikte techniek

- [Laravel: installatie en databaseconfiguratie](https://laravel.com/framework/docs/13.x/installation)
- [Laravel: database seeders](https://laravel.com/framework/docs/13.x/seeding)
- [Endroid QR Code](https://github.com/endroid/qr-code)
