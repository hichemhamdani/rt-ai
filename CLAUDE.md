# CLAUDE.md — Rutherford & Titan Custom Theme

## Objectif du projet

Recréer le site **Rutherford & Titan** (industrial gas supply) en un **thème WordPress custom** nommé `rt-theme`, sans aucun page builder ni plugin tiers hormis **WooCommerce** et **Yoast SEO**.

À la fin du projet, l'utilisateur doit pouvoir **désactiver tous les plugins sauf WooCommerce et Yoast SEO** et voir exactement le même rendu visuel.

---

## Environnement local

- **URL locale** : http://rtclone.local (port HTTP 10041)
- **Base de données** : MySQL port **10042**, host 127.0.0.1, user `root`, password `root`, db `local`
- **Client MySQL** : `C:\Users\hamda\AppData\Roaming\Local\lightning-services\mysql-8.0.35+4\bin\win64\bin\mysql.exe`
- **Répertoire thème** : `wp-content/themes/rt-theme/`
- **Thème actuel** : Hello Elementor (à remplacer par rt-theme)

---

## Plugins à conserver (seulement ces deux)

| Plugin | Raison |
|--------|--------|
| WooCommerce | E-commerce (boutique, produits, panier, commandes) |
| Yoast SEO | SEO, sitemaps, meta tags |

## Plugins à supprimer (remplacés par le thème)

| Plugin | Ce qui le remplace dans le thème |
|--------|----------------------------------|
| Elementor + Elementor Pro | Templates PHP custom |
| JetEngine | `register_post_type()` / `register_taxonomy()` dans `functions.php` |
| JetSmartFilters | Filtres AJAX custom en PHP |
| JetFormBuilder | Formulaires natifs ou iframe GHL (externe) |
| Advanced Custom Fields (ACF) | `get_post_meta()` natif WordPress |
| Code Snippets | Tout le code migré dans `functions.php` ou fichiers dédiés |
| Max Mega Menu | Megamenu custom en PHP/CSS/JS |
| Redirection | Optionnel — peut rester ou être remplacé par `.htaccess` |
| Imagify / SG CachePress / Security Optimizer / ManageWP | Hors scope du thème, décision de l'utilisateur |

---

## Système de design (design tokens)

### Couleurs

```css
--color-dark-blue:   #17376C;   /* Texte principal, titres, fond header */
--color-bright-blue: #0058FC;   /* Liens, accents, boutons */
--color-cyan:        #A1FAFF;   /* Badges, highlights */
--color-yellow:      #FFCE00;   /* Preheadings, étoiles, accents */
--color-black:       #111111;
--color-white:       #FFFFFF;
--color-bg-light:    #F3F6FD;   /* Fond page par défaut */
--color-border:      #E0E0E0;
--color-divider:     #F2F2F2;
```

### Typographie

```css
/* Titres */
font-family: 'Oswald', sans-serif;
/* Corps */
font-family: 'Reddit Sans', sans-serif;
/* H4–H6 */
font-family: 'Fira Sans Condensed', sans-serif;
```

| Élément | Font | Taille | Poids | Transform |
|---------|------|--------|-------|-----------|
| H1 | Oswald | 3rem / 2.5rem / 1.8rem | 400 | uppercase |
| H2 | Oswald | 3rem / 2.5rem / 2rem | 400 | uppercase |
| H3 | Oswald | 1.5rem / 1.3rem | 400 | — |
| H4–H6 | Fira Sans Condensed | — | 500 | — |
| Pre-heading | Reddit Sans | 1.1rem | 400 | uppercase, letter-spacing 0.1em |
| Lead | Reddit Sans | 1.5rem / 1.2rem | 300 | — |
| Paragraphe | Reddit Sans | 1.2rem / 1.1rem / 1rem | 300 | line-height 1.2 |
| Nav | Oswald | 1rem | 400 | uppercase, letter-spacing 0.1em |
| Button | Oswald | 1.3rem (large) / 1rem (small) | 300 / 400 | uppercase |

### Boutons

```css
/* Bouton principal */
background: linear-gradient(90deg, #17376C, #0058FC);
color: #F3F6FD;
padding: 1rem 2rem;
border-radius: 0;
font: Oswald 1.3rem uppercase;

/* Hover */
background: linear-gradient(90deg, #17376C, #0058FC);
color: #FFFFFF;
```

### Layout

```css
--container-max-width: 1280px;
--gap-columns: 3rem;
--gap-rows: 1rem;
```

### Breakpoints

```css
/* Mobile */  @media (max-width: 767px)
/* Tablet */  @media (max-width: 1024px)
/* Desktop */ min-width: 1025px
```

---

## Structure du thème `rt-theme`

```
wp-content/themes/rt-theme/
├── style.css                  # Header du thème
├── functions.php              # Enqueues, menus, CPTs, supports
├── index.php                  # Fallback
├── front-page.php             # Page d'accueil
├── header.php                 # Header HTML
├── footer.php                 # Footer HTML
├── page.php                   # Template page générique
├── single.php                 # Article/post
├── 404.php                    # Page 404
│
├── template-parts/
│   ├── header/
│   │   ├── top-bar.php        # Barre promo + liens
│   │   └── main-nav.php       # Logo + nav + bouton call
│   ├── footer/
│   │   ├── testimonials.php   # Carousel témoignages
│   │   └── footer-cols.php    # Colonnes + copyright
│   └── home/
│       ├── hero.php           # Section 1 — Hero vidéo
│       ├── subnav.php         # Section 2 — Sous-nav ancres
│       ├── expertise.php      # Section 3 — Expertise
│       ├── craft.php          # Section 4 — On-Site Generators
│       ├── smart-systems.php  # Section 5 — Smart Systems
│       ├── services.php       # Section 6 — Services 4 cartes
│       ├── client-logos.php   # Section 7 — Logos clients
│       ├── products.php       # Section 8 — Products carousel
│       ├── faq.php            # Section 9 — FAQ accordion
│       └── consultation.php   # Section 10 — Formulaire
│
├── woocommerce/               # Overrides templates WooCommerce
│   ├── archive-product.php
│   ├── single-product.php
│   └── ...
│
├── assets/
│   ├── css/
│   │   ├── main.css           # Variables + reset + typo + layout
│   │   ├── header.css
│   │   ├── footer.css
│   │   ├── home.css
│   │   ├── woocommerce.css
│   │   └── responsive.css
│   ├── js/
│   │   ├── main.js            # Init global
│   │   ├── header.js          # Sticky + scroll behavior
│   │   ├── carousel.js        # Carousel vanilla JS
│   │   ├── megamenu.js        # Megamenu hover/touch
│   │   ├── faq.js             # Accordion
│   │   └── filters.js         # Filtres produits AJAX
│   └── images/
│       └── logo.svg           # (copié depuis uploads)
│
└── inc/
    ├── post-types.php         # CPTs & taxonomies (ex-JetEngine)
    ├── nav-menus.php          # Enregistrement des menus
    ├── woocommerce.php        # Support & hooks WooCommerce
    └── helpers.php            # Fonctions utilitaires
```

---

## Header — Structure détaillée

### Top bar
- Fond : dégradé blanc → `#0058FC`
- Texte desktop : `"Free Liquid Nitrogen Supply Audit - Save Up To 75% - GO >"` (blanc)
- Texte mobile : `"Get a Free Liquid Nitrogen Supply Audit"` (caché sur desktop)
- Liens droite : "Resources" | "Join Us"
- Se cache au scroll (JS)

### Main nav
- Fond : `#17376CEB` (dark blue, semi-transparent) → devient `#FFFFFFF2` au scroll (>50px)
- Sticky `position: sticky; top: 0`
- Logo gauche : texte "Rutherford Titan" (Oswald 1.6rem) / "RT" sur mobile (2.5rem)
- Menu centre :
  - **Solutions** → megamenu 3 colonnes :
    - LN2 (fond `#FFCE00`) : On-Site LN2 Generators (badge "New models") · True Cost of LN2 · Our Clients · Tech Comparison
    - Gases : Nitrogen · Oxygen · Helium · Hydrogen Generators
    - Storage, Transfer & Safety : Bulk Tanks · Cryogenic Hoses · Safety Systems · Accessories
  - **Clients & Applications**
  - **About Us**
  - **Contact**
- Bouton droit : `"Call (281) 940-3597"` — fond bright blue, bordure blanche 1.5px

---

## Footer — Structure détaillée

### Section testimonials (carousel)
- 3 slides (2 sur tablette)
- Étoiles jaunes `#FFCE00`
- Points de navigation : dark blue inactif / bright blue actif
- 3 témoignages :
  1. Wayne England (IVF Michigan) — "Such an easy system to use, and we save about $35K a year"
  2. Nawfel Oussedik (CryoChambers.com) — "RT team is highly knowledgeable about the cryo market"
  3. Pavel Romashkin (University of Atmospheric Research) — "Robust, works great, and portable"

### Colonnes footer
- **Col 1 — Rutherford & Titan** : Description société + lien LinkedIn (icône blanche)
- **Col 2 — LN2 Generators** : 3 liens avec icône point
- **Col 3 — Useful Links** : Contact Us · Join Us · Follow Your Order

### Bottom bar
- Copyright : "Rutherford Titan — All rights reserved"
- Liens légaux : Terms & Conditions · Return Policy · Privacy Statement

---

## Homepage — 11 sections

| # | Section | Éléments clés |
|---|---------|---------------|
| 1 | **Hero** | Vidéo bg `sky-view-rt.mp4` · fallback `buying-industrial-gas.jpg` · overlay noir 11% · texte bas-gauche (preheading yellow, H1 white, divider yellow, body, button) |
| 2 | **Sub-nav** | 4 ancres sticky : Expertise · Services · Products · Free Consultation · fond bright blue · caché mobile |
| 3 | **Expertise** | 50/50 image+texte · image `Industrial-gases.jpeg` · preheading bleu · H2 · 2 liens action |
| 4 | **On-Site Generators** | Image `RT-Tanks-Large.jpeg` · 2 feature boxes (Save 90% · Guarantee Supply) · fond blanc |
| 5 | **Smart Systems** | Bg image sombre 90% overlay · preheading yellow · H3 white · bouton white |
| 6 | **Services** | 4 cartes (Planning · Maintenance · Installation · Upgrades) · bordures 2px yellow/grey · ombre 20px |
| 7 | **Client Logos** | Carousel 6 logos · auto-scroll · titre "They Trust Us" |
| 8 | **Products** | Carousel WooCommerce products · fond dégradé dark blue→bright blue · preheading yellow · white text |
| 9 | **FAQ** | 6 items accordion · image sticky droite `FAQ-RT.jpeg` · 2 colonnes |
| 10 | **Consultation** | Iframe GHL form · preheading bleu · titre "Talk To An Expert" · bordures 2px cyan |
| 11 | **Footer** | `get_template_part('template-parts/footer/...')` |

---

## Custom Post Types (ex-JetEngine → thème)

À déclarer dans `inc/post-types.php` :

```php
// À confirmer en lisant la DB (wp_posts où post_type NOT IN defaults)
// Probables CPTs d'après les templates Elementor trouvés :
register_post_type('project', [...]);       // Case studies / projets clients
register_post_type('industry', [...]);      // Industries (Dermatology, Pharma, etc.)
register_post_type('resource', [...]);      // Knowledge base / resources
register_post_type('brand', [...]);         // Brands page
```

**À vérifier** : `SELECT DISTINCT post_type FROM wp_posts WHERE post_type NOT IN ('post','page','attachment','revision','nav_menu_item','custom_css','customize_changeset','oembed_cache','user_request','wp_block','wp_template','wp_template_part','wp_global_styles','wp_navigation','elementor_library','e-landing-page') AND post_status != 'auto-draft';`

---

## Menus WordPress à enregistrer

```php
register_nav_menus([
    'primary'    => 'Menu principal',
    'top-bar'    => 'Top bar (Resources, Join Us)',
    'footer-ln2' => 'Footer — LN2 Generators',
    'footer-links'=> 'Footer — Useful Links',
    'footer-legal'=> 'Footer — Legal',
]);
```

---

## Règle absolue — Texte des sections

**À chaque modification de layout ou de style d'une section, conserver STRICTEMENT le texte original :**
- Sur-titre (preheading)
- Titre (h1/h2/h3)
- Tous les paragraphes
- Texte des boutons
- Liens et labels

Ne jamais réécrire, reformuler, raccourcir ou supprimer du contenu textuel sans instruction explicite de l'utilisateur. Seul le style/layout change, pas le contenu.

---

## Points d'attention techniques

### Carousel (témoignages, logos, produits)
- Pas de plugin jQuery — vanilla JS ou CSS scroll snap
- Auto-scroll sur logos et produits
- Touch/swipe support mobile

### Megamenu
- Déclenché au hover (desktop) et au tap (mobile)
- Colonne LN2 avec fond `#FFCE00` et badge "New models" (`background: #A1FAFF`)
- Position absolute sous la nav, pleine largeur

### Sticky header
- JS : à 0px scroll → fond `#17376CEB`, top bar visible
- JS : à >50px scroll → fond `#FFFFFFF2`, top bar cachée, texte passe en `#17376C`

### Formulaire consultation
- Actuellement iframe GHL (GoHighLevel) — URL externe à conserver telle quelle
- Aucune dépendance plugin

### WooCommerce — Section Products (homepage)
- Requête WP_Query avec les produits mis en avant ou une catégorie
- Carousel des produits avec image, titre, prix, bouton

### Videos
- `sky-view-rt.mp4` est dans `wp-content/uploads/` — à localiser avec :
  `SELECT guid FROM wp_posts WHERE post_type='attachment' AND post_mime_type LIKE 'video%';`

---

## Phases de développement

### Phase 1 — Header + Footer + Homepage ← EN COURS
**Résultat attendu** : Désactiver Elementor → homepage identique visuellement

Fichiers à créer :
- `style.css`, `functions.php`, `index.php`
- `header.php`, `footer.php`, `front-page.php`
- `template-parts/header/*`, `template-parts/footer/*`, `template-parts/home/*`
- `assets/css/*`, `assets/js/*`
- `inc/nav-menus.php`

### Phase 2 — Pages WooCommerce
- `woocommerce/archive-product.php` (shop + filtres)
- `woocommerce/single-product.php`
- Panier, Mon Compte, Confirmation
- `inc/woocommerce.php`
- `assets/css/woocommerce.css`

### Phase 3 — Pages statiques
- About, Contact, Join Us, Brands, Return Policy, Privacy, Terms
- Page 404
- Templates pour articles du blog / resources

### Phase 4 — Custom Post Types & Taxonomies
- Industries (Dermatology, Pharma, Oil & Gas, etc.)
- Projects / Case Studies
- Knowledge Base
- `inc/post-types.php`

### Phase 5 — Filtres produits
- Remplacer JetSmartFilters par filtres AJAX custom
- `assets/js/filters.js`
- Endpoint AJAX dans `functions.php`

---

## Réponse à la question de l'utilisateur

> "Si tu finis, je dois être en mesure de désactiver les plugins et de voir le même design ?"

**Oui, à la fin du projet complet (phases 1–5)** :

- Désactiver Elementor, Elementor Pro → aucun impact (tout est en PHP/CSS natif)
- Désactiver JetEngine → aucun impact (CPTs dans le thème)
- Désactiver JetSmartFilters → aucun impact (filtres custom)
- Désactiver ACF → aucun impact (`get_post_meta()` natif)
- Désactiver Code Snippets → aucun impact (code migré dans le thème)

**Plugins qui restent** : WooCommerce + Yoast SEO

**Après Phase 1 seulement** : Homepage, header et footer sont identiques sans Elementor. Les autres pages (shop, produits, contact, etc.) seront encore vides ou cassées jusqu'à la phase correspondante.

---

## Données de référence (DB)

| Élément | ID WordPress |
|---------|-------------|
| Homepage | post_id = 2 |
| Header actif | post_id = 41 (elementor_library, conditions: include/general) |
| Footer actif | post_id = 36 (elementor_library, conditions: include/general) |
| Kit global (design tokens) | post_id = 3 |
| Shop | post_id = 5 |
| Contact | post_id = 16 |
| About | post_id = 2815 |
| Brands | post_id = 2552 |
| Join Us | post_id = 6292 |

Les fichiers JSON exportés se trouvent dans `C:\Users\hamda\Local Sites\rtclone\` :
- `kit_settings.json` — Design tokens globaux
- `header_data.json` — Structure JSON header
- `footer_data.json` — Structure JSON footer
- `homepage_data.json` — Structure JSON homepage

---

## Retour à la version pré-design (IMPORTANT)

L'utilisateur doit pouvoir revenir à l'état du thème **avant toute modification de design** (c'est-à-dire avant que l'on commence à retoucher la hero section et les sections suivantes).

### Mécanisme : Git dans le répertoire du thème

Le répertoire `wp-content/themes/rt-theme/` doit être un dépôt Git avec **au minimum deux commits** :

| Commit | Contenu |
|--------|---------|
| `baseline` | État initial du thème — structure HTML/PHP en place, CSS et JS de base fonctionnels, **avant** toute itération de design |
| `latest` | État courant avec toutes les modifications de design |

### Procédure d'initialisation (à faire une seule fois)

```bash
cd wp-content/themes/rt-theme
git init
git add .
git commit -m "baseline: theme structure before design iterations"
```

Ensuite, chaque session peut créer un nouveau commit pour sauvegarder l'état courant.

### Retour à la version baseline

Quand l'utilisateur dit **"reviens à la version baseline"** ou **"reviens avant les modifications de design"** :

```bash
cd wp-content/themes/rt-theme
git checkout baseline -- .
```

### Règle absolue

**Avant de commencer toute nouvelle session de modifications de design, vérifier que le commit `baseline` existe.** Si ce n'est pas le cas, demander à l'utilisateur de confirmer avant de continuer.
