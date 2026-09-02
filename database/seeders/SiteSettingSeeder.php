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

        // About SIBEA-CI
        SiteSetting::set('about.hero', [
            'title' => 'À propos — SIBEA-CI',
            'subtitle' => 'SARL créée en 2022 — BTP, Électricité, Pétrole et Agro-industrie. Siège Abidjan Bingerville Abatta Lot 935 Îlot 86, près Hôtel Blanc Cerf et carrefour Pantchô. Dirigeant : Ouattara Bassoma Ziegnougo — IDU CI-2022-0016466 Q. Valeurs : sécurité, qualité, innovation.',
            'image' => null,
        ], 'about');

        SiteSetting::set('about.progress', [
            ['label' => 'BTP & GÉNIE CIVIL', 'pct' => 95],
            ['label' => 'ÉLECTRICITÉ', 'pct' => 88],
            ['label' => 'PÉTROLE & ÉNERGIE', 'pct' => 85],
            ['label' => 'AGRO-INDUSTRIE', 'pct' => 90],
        ], 'about');

        SiteSetting::set('about.engagement', [
            'eyebrow' => 'À propos de nous',
            'title' => 'SIBEA-CI — Laboratoire urbain ivoirien',
            'subtitle' => 'Construisons ensemble votre avenir en Côte d\'Ivoire',
            'desc1' => 'Chez SIBEA-CI, nous transformons vos idées en réalité en combinant expertise, innovation et qualité. Spécialistes du BTP, du génie civil et des infrastructures, nous accompagnons nos clients dans la conception et la réalisation de projets durables, adaptés aux exigences techniques et environnementales ivoiriennes.',
            'desc2_title' => 'Des Solutions Adaptées à Tous Vos Projets',
            'desc2' => 'Que vous soyez un particulier, une entreprise ou une collectivité, nous mettons notre savoir-faire à votre service pour construire, rénover ou moderniser vos bâtiments et infrastructures. Nous intervenons dans plusieurs domaines :',
            'items' => [
                'Construction de bâtiments résidentiels et commerciaux.',
                'Travaux publics et infrastructures urbaines (VRD).',
                'Rénovation et réhabilitation de structures existantes.',
                'Génie civil et ouvrages industriels — ACD sécurisé.',
            ],
            'floating' => ['number' => '+10 projets', 'label' => 'Conformité technique à 100%', 'desc' => 'Livrés avec traçabilité totale — zéro litige foncier, zéro défaut de structure.'],
        ], 'about');

        SiteSetting::set('about.valeurs', [
            'title' => 'Nos valeurs fondamentales',
            'subtitle' => 'Des principes techniques et déontologiques stricts au service de la sécurisation de vos investissements fonciers et BTP.',
            'items' => [
                ['title' => 'Intégrité & Rigueur', 'desc' => 'Traçabilité totale, zéro intermédiaire douteux, surveillance rigoureuse ACD, titres fonciers, permis et purges coutumières.', 'icon' => '⬡'],
                ['title' => 'Innovation Durable', 'desc' => 'Matériaux résistants et écologiques, BIM, drone, WebP/AVIF, éco-conception pour durabilité.', 'icon' => '◈'],
                ['title' => 'Accompagnement Humain', 'desc' => 'Écoute, étude personnalisée, suivi de chantier et SAV — de l\'étude à la remise des clés.', 'icon' => '◆'],
            ],
        ], 'about');

        SiteSetting::set('about.pourquoi', [
            'eyebrow' => 'Pourquoi SIBEA-CI ?',
            'title' => 'Pourquoi nous choisir ?',
            'subtitle' => 'Une chaîne d\'expertises intégrée — de l\'audit foncier à la livraison — pour sécuriser chaque étape.',
            'items' => [
                ['title' => 'Un accompagnement personnalisé', 'desc' => 'Chaque projet est unique. Nous étudions vos besoins pour proposer des solutions sur mesure.'],
                ['title' => 'Des matériaux de qualité', 'desc' => 'Matériaux résistants et écologiques pour garantir la durabilité de vos infrastructures.'],
                ['title' => 'Une équipe d’experts qualifiés', 'desc' => 'Ingénieurs, architectes et techniciens engagés pour livrer des ouvrages fiables et conformes aux normes.'],
                ['title' => 'Respect des délais et du budget', 'desc' => 'Nous livrons dans les meilleures conditions, en tenant compte de vos contraintes financières.'],
            ],
            'cta_title' => 'Construisons Ensemble un Avenir Durable',
            'cta_desc' => 'Nous mettons tout en œuvre pour bâtir des infrastructures modernes, solides et respectueuses de l’environnement. Nos solutions intègrent les dernières innovations en matière de construction durable.',
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
