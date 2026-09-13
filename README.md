# Poliklinika KOLF — web

Nový web pro Polikliniku KOLF Pardubice. Vlastní odlehčené WordPress téma podle
návrhu z Claude Design ([Poliklinika Pardubice redesign](https://claude.ai/code/artifact/e5780a89-6f04-4f2b-9173-a8b3f1720489)).

## Filozofie: minimum pluginů

Web běží na **jednom vlastním tématu** (`wp-content/themes/kolf-web`) bez page
builderu a bez SEO/formulářových pluginů:

- Layout a texty jsou přímo v PHP šablonách (žádný Elementor/Divi).
- Oddělení a ambulance (`oddeleni`) a zdravotnické služby (`sluzba`) jsou
  vlastní custom post types s jednoduchými meta boxy — editace v adminu bez
  ACF.
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
├── archive-oddeleni.php     # telefonní seznam — Oddělení / Osoby
├── single-oddeleni.php      # detail jednoho oddělení
├── page.php / index.php     # obecné stránky / fallback
├── inc/
│   ├── post-types.php       # CPT "oddeleni" a "sluzba"
│   ├── meta-boxes.php       # vlastní meta boxy (patro, kontakt, pořadí…)
│   ├── customizer.php       # editovatelné kontaktní údaje (telefon, e-mail, adresa)
│   ├── helpers.php          # dotazy nad CPT (seskupení podle patra, rychlá čísla…)
│   ├── seed-content.php     # jednorázové naplnění 34 oddělení + 5 služeb
│   └── performance.php      # úklid výstupu
└── assets/
    ├── css/style.css        # veškerý vizuální styl, CSS custom properties
    └── js/department-search.js
```

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
   automaticky naplní obsah (34 oddělení, 5 zdravotnických služeb).
4. V **Nastavení → Čtení** nemusíte nic měnit, úvodní stránka se řídí
   šablonou `front-page.php` automaticky.

### Znovu-naplnění obsahu

Pokud smažete seedovaný obsah a chcete ho vrátit, navštivte jako přihlášený
administrátor:

```
/wp-admin/?kolf_reseed=1
```

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
