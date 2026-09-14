# Poliklinika KOLF — web

Nový web pro Polikliniku KOLF Pardubice. Vlastní odlehčené WordPress téma podle
návrhu z Claude Design ([Poliklinika Pardubice redesign](https://claude.ai/code/artifact/e5780a89-6f04-4f2b-9173-a8b3f1720489)).

## Filozofie: minimum pluginů

Web běží na **jednom vlastním tématu** (`wp-content/themes/kolf-web`) bez page
builderu a bez SEO/formulářových pluginů:

- Layout a texty jsou přímo v PHP šablonách (žádný Elementor/Divi).
- Oddělení (`oddeleni`), Osoby (`osoba`), Telefony (`telefon`, jen na pozadí),
  Hodiny (`hodiny`, jen na pozadí) a Zdravotnické služby (`sluzba`) jsou
  vlastní custom post types s jednoduchými meta boxy — editace v adminu bez ACF.
- Živé vyhledávání v oddělení je čistý JS (žádné jQuery, žádná knihovna).
- Jediný externí request navíc je na Google Fonts (Source Serif 4, IBM Plex
  Sans, IBM Plex Mono) — dá se v budoucnu i self-hostovat pro ještě rychlejší
  načtení, viz „Možná vylepšení“ níže.
- `inc/performance.php` odstraňuje standardní WP balast, který web nepoužívá
  (emoji skripty, embed skript, RSD/WLW odkazy…).

Plugin dávejte do `wp-content/plugins/` jen když ho fakt potřebujete
(např. zálohování nebo cache na produkčním hostingu) — ne pro věci, které jde
napsat pár řádky kódu.

## Struktura

```
wp-content/themes/kolf-web/
├── style.css                # hlavička tématu (WP to vyžaduje)
├── functions.php            # bootstrap, enqueue assetů
├── header.php / footer.php
├── front-page.php           # úvodní stránka (hero, hledání, patra, služby, kontakt)
├── archive-oddeleni.php     # kompletní seznam oddělení (podle patra)
├── single-oddeleni.php      # detail jednoho oddělení (+ jeho lidé a telefony)
├── archive-osoba.php        # kompletní seznam osob (podle příjmení)
├── single-osoba.php         # detail jedné osoby (+ oddělení a telefony)
├── page.php / index.php     # obecné stránky / fallback
├── inc/
│   ├── post-types.php       # CPT "oddeleni", "osoba", "telefon", "hodiny", "sluzba"
│   ├── meta-boxes.php       # vlastní meta boxy + opakovatelné seznamy telefonů/hodin
│   ├── customizer.php       # editovatelné kontaktní údaje (telefon, e-mail, adresa)
│   ├── helpers.php          # dotazy nad CPT (patra, telefony, hodiny, osoby podle oddělení…)
│   ├── seed-content.php     # naplnění obsahu z reálné produkční DB (viz níže)
│   ├── data/                # vygenerovaná data z MSSQL exportu (Department/Person/Phone/DayClock)
│   └── performance.php      # úklid výstupu
└── assets/
    ├── css/style.css              # veškerý vizuální styl, CSS custom properties
    ├── css/admin-repeaters.css    # drobné doladění opakovatelných seznamů v adminu
    ├── js/department-search.js
    ├── js/admin-phone-repeater.js  # tlačítko "+ Přidat číslo" v adminu
    └── js/admin-hours-repeater.js  # tlačítko "+ Přidat rozvrh" v adminu
```

### Datový model

```
oddeleni (1) ──── (N) osoba     — osoba patří max. do jednoho oddělení (kolf_department_id)
oddeleni (1) ──── (N) telefon   — telefon patřící oddělení
osoba    (1) ──── (N) telefon   — telefon patřící osobě
oddeleni (1) ──── (N) hodiny    — rozvrh (ordinační/provozní hodiny) patřící oddělení
osoba    (1) ──── (N) hodiny    — rozvrh patřící osobě
```

`telefon` a `hodiny` nemají vlastní stránku ani položku v menu adminu — zadávají
se přímo ve formuláři konkrétního oddělení nebo osoby (tlačítka „+ Přidat
číslo“ / „+ Přidat rozvrh“), v databázi ale žijí jako samostatné záznamy.
Produkční databáze (MSSQL `DayClock`) měla hodiny jen u osob — u oddělení
jde nově přidat hodiny ručně v adminu, migrovaná data tam nejsou.

**CMS stránky** (staré MSSQL tabulky `Page` + `ContentItem` + `MenuItem`) jsou
namigrované beze změny struktury — na to už WordPress svoje nástroje má:
- `Page` + `ContentItem` (spárováno přes `ContentItem.Key = 'CmsPage' + Page.Id`)
  → obyčejné WP **Stránky** (Stránky → Všechny stránky), staré URL (`ShortUrlId`)
  zachované jako slug.
- `MenuItem` → nativní WP **Menu** nazvané „CMS stránky (import)“ (Vzhled → Menu),
  se stejnou hierarchií jako na starém webu. Je přiřazené k nové pozici
  `legacy_pages`, ale šablona ho zatím nikde nevykresluje — kam (a jestli
  vůbec) ho zobrazit nechávám na vás, klidně ho i přeorganizujte přetažením
  přímo ve Vzhled → Menu.

Produkční databáze (MSSQL) neměla u osoby přímý cizí klíč na oddělení —
při migraci (`inc/data/persons.php`) se spároval podle shodného čísla dveří,
u pár desítek případů, kde se čísla neshodovala přesně, ručně podle patra a
specializace (viz komentáře v souboru). V nové databázi je to ale uložené
jako běžný cizí klíč, který si můžete v adminu u osoby kdykoliv přepnout.

## Lokální vývoj

### Varianta A — `wp-env` (Docker, doporučeno)

Vyžaduje [Docker Desktop](https://www.docker.com/products/docker-desktop/) a Node.js.

```bash
npm install -g @wordpress/env
wp-env start
```

Web pak poběží na `http://localhost:8888` (admin na `/wp-admin`,
uživatel/heslo `admin` / `password`). Konfigurace je v `.wp-env.json`.

### Varianta B — vlastní WP instalace (Local, XAMPP, …)

1. Nainstalujte čistý WordPress (poslední verze).
2. Zkopírujte/nalinkujte `wp-content/themes/kolf-web` do
   `wp-content/themes/` vaší instalace.
3. V adminu aktivujte téma **Poliklinika KOLF** — při aktivaci se
   automaticky naplní obsah reálnými daty z produkce (47 oddělení, 63 osob,
   telefony, ordinační hodiny, 5 zdravotnických služeb, 24 CMS stránek a menu).
4. V **Nastavení → Čtení** nemusíte nic měnit, úvodní stránka se řídí
   šablonou `front-page.php` automaticky.

### Znovu-naplnění obsahu

Pokud smažete seedovaný obsah a chcete ho vrátit, navštivte jako přihlášený
administrátor:

```
/wp-admin/?kolf_reseed=1
```

Pokud jste téma aktivovali už dřív (s první, ukázkovou verzí dat) a teď jen
aktualizujete soubory tématu, aktivace se znovu nespustí sama — použijte
tenhle odkaz ručně, ať se stará ukázková oddělení nahradí reálnými daty
a založí se nové typy obsahu Osoby/Telefony.

## Co je potřeba doplnit před spuštěním

- **Foto/mapa budovy** v sekci Kontakt — zatím je tam placeholder. Nastavte
  „Vyobrazený obrázek“ na úvodní stránce (Stránky → upravit domovskou
  stránku, pokud ji používáte jako static page) nebo doplňte v
  `front-page.php`.
- **Logo** — přes Přizpůsobit → Identita webu (Custom Logo), jinak se
  zobrazuje textové logo „Poliklinika KOLF“.
- **Hlavní menu** — pokud chcete jiné pořadí/položky než výchozí (Oddělení,
  Osoby, Zdravotnické služby, Kontakt), založte menu ve Vzhled → Menu a
  přiřaďte ho pozici „Hlavní navigace“.
- Zkontrolujte kontaktní údaje v **Přizpůsobit → Kontaktní údaje** (telefon,
  e-mail, adresa, rok založení, odkaz na mapu).

## Možná vylepšení (mimo rozsah prvního nasazení)

- Self-hostování fontů (odstranit poslední externí request na Google Fonts).
- Statické generování/caching stránky pro produkci (bez pluginu — např. přes
  hosting-level cache nebo `nginx`/Cloudflare).
- Formulář pro kontakt, pokud bude potřeba (řešit vlastním `wp_mail()`
  handlerem, ne pluginem typu Contact Form 7 / WPForms).
