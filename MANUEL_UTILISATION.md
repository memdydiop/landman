# SIBEA-CI — Manuel d'utilisation
**BTP · Aménagement Urbain (VRD) · Lotissement**
Version 1.0 — Laravel 13 / Livewire / PostgreSQL / Spatie Permissions
Date : 27 août 2026

---

## 1. Introduction

SIBEA-CI est une plateforme vitrine haut de gamme + backoffice pour gérer les 3 activités : **BTP & Construction**, **Aménagement Urbain (VRD)** et **Lotissement & Foncier**. Le site capte des prospects qualifiés (particuliers, entreprises, collectivités) et permet leur traitement centralisé.

**URLs principales**
- Vitrine : `https://sibea-ci.ci/` (ou `http://localhost:8000`)
- Backoffice : `https://sibea-ci.ci/admin` (auth requise)
- Connexion : `/login` — Inscription désactivable via Fortify

**Stack** : Laravel 13, Livewire 4 + Flux 2, Tailwind 4, PostgreSQL, Spatie Laravel Permission, Vite 8.

---

## 2. Première installation (Administrateur technique)

```bash
composer install
cp .env.example .env
# Éditer .env : DB_CONNECTION=pgsql DB_DATABASE=landman DB_USERNAME=postgres DB_PASSWORD=xxx APP_NAME=SIBEA-CI
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

**Seed par défaut** `database/seeders/DatabaseSeeder.php:20` :
| Email | Mot de passe | Rôle |
|---|---|---|
| `admin@landman.test` | `password` | Super Admin (tous droits) |
| `editeur@landman.test` | `password` | Éditeur BTP |
| `commercial@landman.test` | `password` | Commercial Lotissement |
| `test@example.com` | `password` | Aucun rôle (test) |

> Changer immédiatement les mots de passe en production via `admin/users`.

---

## 3. Rôles & Permissions `config/permission.php:1` `RolesAndPermissionsSeeder.php:12`

| Rôle | Ce qu'il peut faire |
|---|---|
| **Super Admin** | Tout : `programs.*` `plots.*` `projects.*` `media.manage` `inquiries.*` `users.*` |
| **Éditeur BTP** | `projects.view/create/update/delete/publish` `media.manage` + lecture `programs/plots` `inquiries.view` |
| **Commercial Lotissement** | `programs.*` `plots.*` `inquiries.view/update/export` + lecture `projects` |
| **Éditeur** (optionnel) | Lecture seule projets/programs + `projects.update` |

Le menu latéral `resources/views/layouts/app/sidebar.blade.php:20` masque automatiquement les entrées sans permission (`@can`). Les routes `admin/users` exigent `role:Super Admin` `routes/admin.php:12`, l'export `permission:inquiries.export`.

**Gérer les droits** : `Admin > Utilisateurs` → cocher/décocher les rôles par utilisateur `pages/admin/users/index.blade.php:11` `toggleRole()`. Les permissions sont héritées des rôles (Spatie `HasRoles` `app/Models/User.php:38`).

---

## 4. Backoffice — Tableau de bord `GET admin` `pages/admin/dashboard.blade.php:13`

Après connexion `Dashboard` → `Backoffice` dans la sidebar.
Affiche :
- **Programmes** (total)
- **Lots disponibles / vendus** `PlotStatus::DISPONIBLE` `PlotStatus::VENDU`
- **Projets** (total)
- **Prospects nouveaux** `InquiryStatus::NOUVEAU`
- 5 derniers prospects + accès rapides `Nouveau programme/projet` `Exporter CSV`.

---

## 5. Gestion des Programmes (Lotissements) `GET admin/programs`

**Lister** `pages/admin/programs/index.blade.php:38` : recherche `titre/slug like` + filtre `ville` `WithPagination 12`. Colonnes : Programme/slug, Ville, Lots (total · dispo), Surface totale, Publié/Brouillon, Actions `Lots` `Éditer` `Publier/Dépublier` `Supprimer`.

**Créer** `GET admin/programs/create` `pages/admin/programs/form.blade.php:11` :
1. Titre * (auto-génère le slug `Str::slug`)
2. Slug * unique
3. Ville * + Surface totale (m²) + Adresse + Description
4. `Publié` (visible vitrine) → `published_at` auto
5. Image couverture `image max 4Mo` → stockée `programs/covers` + variante `WebP` via `ImageService.php:14`
6. `Créer`

**Éditer** `GET admin/programs/{program}/edit` : mêmes champs, `cover_path` actuel affiché.

**Supprimer** : cascade `plots` (FK `cascadeOnDelete`).

---

## 6. Gestion des Lots `GET admin/programs/{program}/plots` `pages/admin/plots/index.blade.php:11`

Accessible via bouton `Lots` depuis la liste programmes.

**Créer un lot** (bouton `Nouveau lot`) :
- Référence * unique ex `LOT-A12` (majuscule auto)
- Surface m² * + Prix FCFA (optionnel) + Statut * `Disponible/Réservé/Vendu/Option` `PlotStatus.php:7` + Viabilisé checkbox + Statut juridique (ACD) + Plan PDF `mimes:pdf max 8Mo` → `plots/plans` `public`.

**Édition rapide inline** : bouton `Éditer` → champs `surface/prix/status` éditables → `Enregistrer` `saveInline()` `authorize('plots.update')`. Idéal pour mise à jour prix/disponibilité en masse.

**Plan PDF** : lien `PDF` `Storage::disk('public')->url()` ouvre le plan dans un nouvel onglet. Vitrine : même lien.

**Supprimer** `authorize('plots.delete')`.

*Astuce* : le compteur `dispo` sur programme et les badges `Disponible` en vitrine reflètent immédiatement le `status`.

---

## 7. Gestion des Réalisations BTP/Aménagement `GET admin/projects`

**Lister** `pages/admin/projects/index.blade.php:38` : recherche + filtres `ServiceType/BTP|AMENAGEMENT|LOTISSEMENT` `ProjectStatus/EN_COURS|LIVRE|A_VENIR` `12/p`. Colonnes : Projet (+ À la une), Service, Statut `badgeColor()`, Localisation/surface, Médias count, Actions `Éditer` `Épingler/Désépingler` `Publier/Dépublier` `Supprimer`.

**Créer/Éditer** `pages/admin/projects/form.blade.php:12` :
1. Titre * → slug * unique
2. Service * `BTP / Aménagement VRD` + Statut * `En cours/Livré/À venir` (Enums)
3. Localisation + Surface m² + Durée mois + Année
4. Description + Fiche technique JSON ex `{"maitre_ouvrage":"Sonatel","budget":"1 200 000 €"}` → `array` cast
5. `Mettre à la une` (hero) + `Publié`
6. Couverture `image 5Mo` + Galerie `10×5Mo` `multiple` → `ImageService::storeOptimized` `projects/covers|gallery` `webp` + `position` auto incrémentale
7. `Créer`

**Galerie** (édition) : grille `ordered()` 4 cols `path/position` + `↑/↓` `moveMedia` swap `position` + `Supprimer` `Storage::delete`.

---

## 8. Prospects `GET admin/inquiries` `pages/admin/inquiries/index.blade.php:38`

**Lister** `with(program,plot)` `latest paginate 15` : recherche `name/email like` + filtre `Statut InquiryStatus/NOUVEAU|EN_COURS|TRAITE|ARCHIVE` + `Type InquiryType/DEVIS_BTP|ACHAT_LOT|PARTENARIAT|CONTACT`. Colonnes : Date, Prospect (name/email/phone/service), Type, Lot/Programme, Message tronqué 80, Statut `badgeColor()`.

**Actions** : `select` inline `updateStatus(id,status)` `Rule::in(InquiryStatus)` `authorize('inquiries.update')` + `Supprimer`.

**Exporter** `GET admin/inquiries/export?status&type` `InquiryExportController.php:13` `StreamedResponse` `fputcsv` `chunk 500` `with program/plot` → `prospects-YYYY-MM-DD.csv` colonnes `ID/Date/Nom/Email/Téléphone/Type/Service/Statut/Programme/Lot/Message`. Bouton `Exporter CSV` respecte les filtres actifs.

---

## 9. Utilisateurs `GET admin/users` `pages/admin/users/index.blade.php:11` (Super Admin seul)

`with(roles)` `search like name/email` `paginate 15` : Utilisateur (name/email/vérifié), Rôles (checkbox `toggleRole` `assignRole/removeRole` `authorize('users.update')`), Permissions héritées (6 + `+N`), `Supprimer` sauf soi-même.

---

## 10. Vitrine — Navigation publique `resources/views/layouts/front.blade.php:1`

Header sticky `SIBEA-CI` + nav `Accueil | Lotissements | Réalisations | À propos | Contact` `routeIs` `text-amber-600` + CTA `Demander un devis` + `Connexion/Dashboard`. Footer 4 cols réassurance. **WhatsApp flottant** `components/front/whatsapp-float.blade.php:1` `fixed bottom-6 right-6 bg-emerald-600` `wa.me/2250700000000` sur toutes les pages.

**Accueil** `GET /` `pages/front/home.blade.php:11` : hero `30 ans / Agréments / Décennale` + stats `projects LIVES sum(total_area) plots_available`, 3 expertises `ServiceType`, projets à la une `published()->featured()->limit3` `cover lazy scale-105`, terrains dispo `Plot::available()->limit6`, témoignages, CTA `bg-amber-600`.

**Réalisations** `GET realisations` `pages/front/projects/index.blade.php:11` `#[Url] search/service/status` `Project::published()->when(...) paginate 12` filtres `debounce 300ms` + reset, cartes `cover` `badge service/status` `Str::limit 90`.

**Fiche Projet** `GET realisations/{project:slug}` `pages/front/projects/show.blade.php:11` `getRouteKeyName slug` `abort_unless is_published 404` `load media` : cover + grille `media ordered` 3 cols, badges, fiche `location/surface/duration/year` `description` `technical_sheet Str::headline` `CTA devis similaire contact?project=`.

**Catalogue Lotissements** `GET lotissements` `pages/front/programs/index.blade.php:11` `withCount total/available/reserved/sold` `when search/city/availability whereHas DISPONIBLE` `paginate 9` : cover fallback `city` gradient `plots_available dispo` etc.

**Fiche Programme** `GET lotissements/{program:slug}` `pages/front/programs/show.blade.php:11` : cover + `title/city/address/total_area/description` counts `4 cases` `Réserver` `contact?program` `WhatsApp wa.me?text=...`, tableau lots `when search reference like + status` `paginate 12` `reference mono surface/price/viabilisé/status badge/plan PDF Storage::url` `Réserver` si `DISPONIBLE` → `contact?plot=&program=`.

**À propos** `GET a-propos` : dirigeant, certifications `Agréments/ACD/Décennale/RSE`, chiffres `30+ ans/50ha/300+ familles`.

**Contact** `GET contact` `pages/front/contact.blade.php:10` `<livewire:front.inquiry-wizard />` `App\Livewire\Front\InquiryWizard.php:17` :

**Wizard 3 étapes** :
1. *Besoin* radio `DEVIS_BTP/ACHAT_LOT/PARTENARIAT/CONTACT` + `service_type` auto `updatedInquiryType` `validateStep1`.
2. *Détails* si `DEVIS_BTP → location/surface/budget` si `ACHAT_LOT → program* select published + plot select plotsForProgram Computed` `selectedPlot` `plan PDF` + `budget`.
3. *Coordonnées* `name*/email*/phone/message/rgpd accepted` `validate` `Inquiry::create status NOUVEAU meta ip/budget/surface/location/step` `array_filter` → success `Référence #id` + `Télécharger plan PDF` si lot + `WhatsApp`.

Pré-remplissage `?plot=&program=&project=&type=` `mount()` `Plot::with program`.

---

## 11. Médias & Performance

- Upload `image` `max 4-5Mo` `accept image/*` `WithFileUploads` → `ImageService::storeOptimized` `GD imagewebp 82%` `.webp` sinon fallback.
- Front `loading=lazy` `Storage::url` `group-hover:scale-105` `srcset` helper disponible.
- `pint` `laravel` + `phpstan level 7` 0 erreur + `php artisan test` 61/61.

---

## 12. Maintenance & Support

```bash
php artisan migrate:fresh --seed # reset + demo 3 programs/30 plots/9 projects
php artisan tinker # ex: Program::published()->count()
php artisan route:list | grep admin
./vendor/bin/pint && ./vendor/bin/phpstan analyse
```

**RGPD** : champ `rgpd accepted` obligatoire wizard, `meta ip` stocké, données non partagées footer. Suppression prospect `softDeletes` possible.

**Support** : logs `storage/logs/laravel.log` `MAIL_MAILER=log` en dev (`.env:50`).

---

*Document généré pour SIBEA-CI — toute modification de permissions nécessite `php artisan permission:cache-reset` (Spatie).*
