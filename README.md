# QR-Game

QR-Game is een webapplicatie voor een schoolspel. Studenten scannen QR-codes, beantwoorden vragen en verzamelen punten. De organisator beheert de vragen, beoordeelt open antwoorden en bekijkt de resultaten.

De interface is in het Nederlands. Het project gebruikt Laravel, Blade, MySQL, CSS en JavaScript.

> **Concept voor oplevering:** de beschrijving en gebruiksinstructies zijn uitgewerkt. De installatie van de spelgegevens moet nog worden aangevuld na controle van de overige seeders. De huidige `DatabaseSeeder.php` maakt alleen `Test User` aan en levert nog geen speelbare demo op. Een volledige installatie op een lege database is nog niet gecontroleerd.

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
- Vragen toevoegen en bewerken wanneer de spelstatus dit toestaat.
- Per vraag een QR-code bekijken en als SVG downloaden.
- Het spel starten, pauzeren, hervatten en stoppen.
- Open antwoorden beoordelen met punten en feedback.
- Resultaten bekijken en downloaden als CSV.

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

### 3. Spelgegevens klaarzetten — nog te controleren

De aangeleverde `database/seeders/DatabaseSeeder.php` maakt alleen een gebruiker met de naam `Test User` en het e-mailadres `test@example.com` aan. Deze seeder maakt geen spel, vragen of antwoordopties aan en roept geen andere seeders aan.

Daarom is alleen `php artisan db:seed` op dit moment niet voldoende om dezelfde demo als in de ontwikkelomgeving te krijgen. De juiste seeder en de daarbij horende lokale inloggegevens moeten hier nog worden toegevoegd. De naam of het wachtwoord van een bestaand demo-account mag niet worden afgeleid uit deze standaardseeder.

De verdere instructies voor spelen en beheer gaan uit van een database waarin een organisator, een gekoppeld spel en vragen aanwezig zijn.

### 4. Website starten

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Open [http://localhost:8000](http://localhost:8000). Laat de terminal open zolang je de website gebruikt.

Met een bestaande Herd-configuratie kan het project ook via [http://qr-game.test](http://qr-game.test) worden geopend. Gebruik binnen een test steeds hetzelfde adres: `qr-game.test`, `localhost` en het IP-adres hebben afzonderlijke browsersessies.

## Gebruik

### Organisator

1. Open `/beheer/inloggen` en log in met het voorbereide organisatoraccount.
2. Open **Vragen**. Voeg vragen toe of bewerk ze zolang dat is toegestaan.
3. Open de bewerkpagina van een vraag om de QR-code te bekijken of te downloaden.
4. Start het spel via het **Dashboard**.
5. Beoordeel open antwoorden via **Nakijken**. Meerkeuzevragen krijgen automatisch punten.
6. Bekijk **Resultaten** en kies **Download CSV** om de uitslag te exporteren.

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

**Stoppen beëindigt het spel.** Gebruik pauzeren als je later verder wilt spelen. De ranglijst kan nog veranderen zolang open antwoorden op beoordeling wachten.

## Testen op een telefoon via wifi

Verbind laptop en telefoon met hetzelfde lokale netwerk. Zoek met `ipconfig` het IPv4-adres van de laptop.

In de uitgevoerde test was dat `192.168.2.13`. Vervang dit voorbeeld door jouw huidige adres:

```powershell
php artisan serve --host=192.168.2.13 --port=8000
```

Open daarna [http://192.168.2.13:8000](http://192.168.2.13:8000) op de telefoon. Sta PHP indien nodig toe op het **privénetwerk** in de Windows-firewall.

Open ook het beheergedeelte op de laptop via dit IP-adres. Ga naar `/beheer/vragen` en open de QR-code van een vraag. Controleer vóór het scannen dat de link hetzelfde IP-adres en poortnummer gebruikt. Een QR-code met `qr-game.test` of `localhost` verwijst op de telefoon niet automatisch naar de laptop.

Deze lokale test gebruikt HTTP. De browser kan daarom waarschuwen bij het versturen van gegevens. Gebruik lokale testgegevens; voor gebruik via internet is HTTPS met een geldig certificaat nodig. De ontwikkelserver is bedoeld voor lokaal testen.

## Belangrijke pagina's

De paden hieronder komen achter het adres van de website. Vervang `{game}` door een bestaand spel-ID en `{qr_token}` door het token uit de QR-link.

| Pagina | Pad |
| --- | --- |
| Homepage | `/` |
| Inloggen organisator | `/beheer/inloggen` |
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
| `app/Models/` | Modellen voor onder andere spellen, vragen, deelnemers en antwoorden. |
| `resources/views/` | Blade-pagina's voor de website. |
| `resources/views/student/partials/nav.blade.php` | Navigatie voor de student. |
| `public/css/qr-game.css` | Vormgeving van QR-Game. |
| `public/js/student-answer.js` | Versturen van antwoorden en afhandeling van verzendproblemen. |
| `routes/web.php` | Webroutes. |
| `database/migrations/` | Opbouw van de database. |
| `database/seeders/` | Aanmaken van begin- of testgegevens. |

## Controles en aandachtspunten

Tijdens de ontwikkeling zijn onder andere antwoorden, handmatige beoordeling, ranglijst, CSV-export en foutpagina's in de browser gecontroleerd. Ook het opnieuw openen van een tabblad en het openen van een vraag via een QR-code op de telefoon zijn handmatig getest. Dit is geen bewijs dat alle mogelijke situaties of alle automatische tests zijn geslaagd.

Voor oplevering moet de installatie op een aparte, lege database nog worden uitgevoerd, inclusief het voorbereiden van de spelgegevens. De bestaande database met resultaten moet daarbij behouden blijven.

Praktische aandachtspunten:

- Het CSV-bestand gebruikt een puntkomma als scheidingsteken. Importeer studentnummers in Excel als **tekst** om nullen aan het begin te behouden.
- Een melding dat opslaan niet is bevestigd betekent dat de uitkomst onzeker is. Controleer de verbinding en probeer opnieuw; een bestaand antwoord hoort niet dubbel te worden toegevoegd.
- Een studentnummer is de toegang tot voortgang in dit prototype. Het is geen wachtwoord of sterke identiteitscontrole. Gebruik voor demonstraties fictieve gegevens.
- Voor publieke inzet moeten onder andere HTTPS, productie-instellingen en toegang tot studentgegevens worden beoordeeld.

## Documentatie van gebruikte techniek

- [Laravel: installatie en databaseconfiguratie](https://laravel.com/framework/docs/13.x/installation)
- [Laravel: database seeders](https://laravel.com/framework/docs/13.x/seeding)
- [Endroid QR Code](https://github.com/endroid/qr-code)
