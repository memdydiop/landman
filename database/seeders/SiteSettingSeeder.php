<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Home — hero SIBEA-CI
        SiteSetting::set('home.hero', [
            'badge' => 'SARL depuis 2022 — IDU CI-2022-0016466 Q — Agréments — Garantie décennale',
            'title_line1' => 'SIBEA-CI',
            'title_line2' => 'Bâtir l\'avenir',
            'title_line3' => 'en Côte d\'Ivoire',
            'subtitle' => 'Multisectorielle BTP, Électricité, Pétrole et Agro-industrie. Siège Abidjan Bingerville Abatta Lot 935 Îlot 86 — Dirigeant Ouattara Bassoma Ziegnougo.',
            'cta_primary' => 'NOS RÉALISATIONS',
            'cta_secondary' => 'DEMANDER UN DEVIS',
            'slide1_image' => null,
            'slide2_image' => null,
        ], 'home');

        SiteSetting::set('home.stats', [
            'projects_completed' => 1240,
            'happy_clients' => 1750,
            'workers' => 984,
            'awards' => 96,
        ], 'home');

        SiteSetting::set('home.why_choose', [
            'title' => 'POURQUOI NOUS CHOISIR ?',
            'items' => [
                ['label' => 'Des équipes aux années d\'expérience', 'desc' => '30 ans cumulés, chefs de chantier certifiés, formation sécurité continue. Chantiers Abidjan, Bouaké, Yamoussoukro.'],
                ['label' => 'Une qualité qui perdure après la livraison', 'desc' => 'SAV, garantie décennale, suivi VRD post-livraison, entretien voirie.'],
                ['label' => 'Nous utilisons la technologie pour aller plus vite', 'desc' => 'BIM, drone relevé topo, WebP/AVIF, Lean construction pour délais tenus.'],
                ['label' => 'Nos équipes formées en continu à la sécurité', 'desc' => 'RSE, EPI, sécurité chantier, normes ivoiriennes.'],
            ],
        ], 'home');

        SiteSetting::set('home.team', [
            ['name' => 'Ouattara Bassoma Ziegnougo', 'role' => 'Gérant — SARL', 'avatar' => 'cms/team/BzR6Djdb8CcshEB9hIiWvF4GDexN5noISJ8nOKyl.webp'],
            ['name' => 'Kouamé Yao', 'role' => 'Ingénieur Civil VRD', 'avatar' => null],
            ['name' => 'Awa Koné', 'role' => 'Conductrice Travaux', 'avatar' => null],
            ['name' => 'Diabaté Moussa', 'role' => 'Électricien Chef', 'avatar' => null],
        ], 'home');

        // About SIBEA-CI
        SiteSetting::set('about.hero', [
            'title' => 'LABORATOIRE URBAIN & INGENIERIE BTP',
            'body' => 'Génie civil, aménagement foncier, VRD et construction clé en main en Côte d\'Ivoire. Sécurisation juridique ACD et traçabilité technique.',
            'subtitle' => 'Génie civil, aménagement foncier, VRD et construction clé en main en Côte d\'Ivoire. Sécurisation juridique ACD et traçabilité technique.',
            'badge' => 'SIBEA-CI • EXPERTISE & AMÉNAGEMENT URBAIN',
            'image' => null,
        ], 'about');

        SiteSetting::set('about.progress', [
            ['label' => 'BTP & GÉNIE CIVIL', 'pct' => 95],
            ['label' => 'VOIRIES & VRD', 'pct' => 92],
            ['label' => 'GÉODÉSIE & AMÉNAGEMENT FONCIER', 'pct' => 88],
            ['label' => 'OUVRAGES & RÉSIDENCES', 'pct' => 90],
        ], 'about');

        SiteSetting::set('about.engagement', [
            'eyebrow' => 'NOTRE ENGAGEMENT DE CHANTIER',
            'title' => 'SIBEA-CI — Ingénierie & Laboratoire Foncier',
            'subtitle' => 'Un contrôle rigoureux de l\'audit foncier à la livraison clé en main',
            'desc1' => 'Nous sécurisons chaque étape de vos projets immobiliers et de BTP en appliquant des normes de construction strictes et un suivi géodésique de précision.',
            'desc2_title' => 'Maîtrise d\'Ouvrage Déléguée & Supervision',
            'desc2' => 'Particuliers, entreprises et collectivités : nous garantissons l\'exécution dans le respect des coûts, du cahier des charges et des délais.',
            'items' => [
                'Construction de bâtiments résidentiels, tertiaires et industriels.',
                'Aménagement foncier, viabilisation complète (VRD) et voiries.',
                'Rénovation lourde et réhabilitation d\'ouvrages d\'art.',
                'Audit foncier préalable et sécurisation des arrêtés de concession définitive (ACD).'
            ],
            'floating' => ['number' => '+100', 'label' => 'HECTARES AMÉNAGÉS', 'desc' => 'Avec traçabilité cadastre & ACD.'],
        ], 'about');

        SiteSetting::set('about.valeurs', [
            'title' => 'NOS VALEURS FONDAMENTALES',
            'subtitle' => 'Des standards techniques et déontologiques stricts pour sécuriser vos investissements.',
            'items' => [
                ['title' => 'Traçabilité & Rigueur', 'desc' => 'Audit systématique des sols, vérification administrative et suivi en temps réel des chantiers.', 'icon' => '📐'],
                ['title' => 'Normes BTP & Durabilité', 'desc' => 'Matériaux certifiés, respect des normes parasismiques et études géotechniques approfondies.', 'icon' => '🏗️'],
                ['title' => 'Garantie & Conformité', 'desc' => 'Livraison dans les délais impartis avec délivrance des certificats de conformité technique.', 'icon' => '🛡️'],
            ],
        ], 'about');

        SiteSetting::set('about.pourquoi', [
            'eyebrow' => 'POURQUOI CHOISIR SIBEA-CI',
            'title' => 'Une chaîne d\'expertises techniques intégrées',
            'subtitle' => 'De la topographie à la remise des clés, nous centralisons tous les corps d\'état.',
            'items' => [
                ['title' => 'Études géotechniques & VRD', 'desc' => 'Analyse des sols et viabilisation complète avant toute construction.'],
                ['title' => 'Accompagnement juridique & ACD', 'desc' => 'Purge des droits coutumiers et sécurisation des titres fonciers.'],
                ['title' => 'Supervision rigoureuse', 'desc' => 'Conducteurs de travaux dédiés et reporting d\'avancement systématique.'],
                ['title' => 'Maîtrise budgétaire', 'desc' => 'Devis fermes sans réévaluation imprévue en cours de chantier.'],
            ],
            'cta_title' => 'Bâtissons des Infrastructures Durables',
            'cta_desc' => 'Nos équipes d\'ingénieurs et de techniciens qualifiés déploient les meilleures solutions pour vos projets en Côte d\'Ivoire.',
        ], 'about');

        SiteSetting::set('about.equipe', [
            'badge' => 'ÉQUIPE & GOUVERNANCE PLURIDISCIPLINAIRE',
            'title' => 'Une chaîne d\'expertises intégrée de bout en bout',
            'subtitle' => 'Une équipe pluridisciplinaire mobilisée autour de chaque opération pour sécuriser l\'investissement, maîtriser l\'exécution et valoriser durablement le patrimoine.',
        ], 'about');

        SiteSetting::set('about.cta', [
            'title' => 'Parlons de votre projet',
            'subtitle' => 'Devis sous 24h — BTP, VRD, lotissement, énergie.',
            'button_label' => 'CONTACTER SIBEA-CI →',
            'button_url' => '/contact',
        ], 'about');

        SiteSetting::set('seo', [
            'home_title' => 'SIBEA-CI — BTP, Électricité, Pétrole, Agro-industrie | Abidjan Bingerville',
            'home_desc' => 'SIBEA-CI — BTP, électricité, pétrole, agro-industrie. Terrains viabilisés Bingerville Abatta, BTP & VRD Abidjan. ACD sécurisé.',
            'about_title' => 'À propos — SIBEA-CI | Laboratoire Urbain Abidjan',
            'about_desc' => 'SARL 2022 Bingerville — laboratoire BTP, VRD, lotissement. Sécurité, qualité, innovation.',
            'services_title' => 'Nos Services — BTP, Électricité, Pétrole, Agro',
            'services_desc' => '6 pôles transversaux SIBEA-CI — BTP, électricité, pétrole & énergie, agro-industrie à Abidjan.',
        ], 'seo');

        SiteSetting::set('theme', [
            'primary' => '#003366',
            'accent' => '#004080',
        ], 'theme');

        SiteSetting::set('services.hero', [
            'title' => 'Construisons ensemble',
            'body' => 'De la conception 2D/3D à la livraison à Bingerville. Découvrez nos 6 expertises.',
            'badge' => 'SERVICES — 6 EXPERTISES',
            'image' => null,
        ], 'services');

        // Services SIBEA-CI — 6
        SiteSetting::set('services.list', [
            ['key' => 'btp', 'title' => 'Génie Civil', 'desc' => 'Construction d\'ouvrages d\'art, bâtiments résidentiels et commerciaux, infrastructures urbaines et travaux publics avec une approche technique avancée.', 'image' => null],
            ['key' => 'amenagement', 'title' => 'Achat et vente de terrain', 'desc' => 'Nous vous accompagnons dans l\'acquisition et la cession de terrains adaptés à vos projets, avec un suivi professionnel et sécurisé à chaque étape. Lotissements Bingerville Abatta.', 'image' => null],
            ['key' => 'lotissement', 'title' => 'Ingénierie et Conception', 'desc' => 'Études techniques détaillées, conception de projets innovants, conception de plan en 2D et 3D, planification stratégique et supervision experte des travaux.', 'image' => null],
            ['key' => 'renovation', 'title' => 'Rénovation et Réhabilitation', 'desc' => 'Rénovation complète de bâtiments existants, réhabilitation structurelle avancée et modernisation d\'infrastructures pour une nouvelle vie.', 'image' => null],
            ['key' => 'architecture', 'title' => 'Gestion de Projets', 'desc' => 'Gestion complète de projets de construction, d\'aménagement et de lotissement. Du planning initial à la livraison finale.', 'image' => null],
            ['key' => 'electricite', 'title' => 'Construction Durable', 'desc' => 'Approche écologique innovante et utilisation de matériaux durables pour des constructions respectueuses de l\'environnement. VRD, normes ivoiriennes.', 'image' => null],
        ], 'services');
    }
}
