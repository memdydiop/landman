<?php

use App\Models\SiteSetting;
use App\Services\ImageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] #[Title('CMS — Tout-en-un')] class extends Component {
    use WithFileUploads;

    #[Url]
    public string $activeTab = 'home';

    public int $page = 1;
    public int $perPage = 6;

    // Home hero
    public string $hero_badge = '';
    public string $hero_title1 = '';
    public string $hero_title2 = '';
    public string $hero_title3 = '';
    public string $hero_subtitle = '';
    public string $cta_primary = '';
    public string $cta_secondary = '';
    public $hero_slide1;
    public $hero_slide2;
    public ?string $hero_slide1_existing = null;
    public ?string $hero_slide2_existing = null;

    // Home stats
    public int $stat_projects = 1240;
    public int $stat_clients = 1750;
    public int $stat_workers = 984;
    public int $stat_awards = 96;
    public int $stat_surface = 984000;

    // Home why choose
    public string $why_title = 'POURQUOI NOUS CHOISIR ?';
    public array $why_items = [];

    // Home — NOS SERVICES / Banner / Team (éditables)
    public array $home_offers = [];
    public array $home_details = [];
    public string $banner_title = 'Entrepreneurs & Conducteurs de travaux depuis 1981';
    public string $banner_cta_label = 'DEMANDER UN DEVIS';
    public string $banner_cta_url = '/contact';
    public $banner_image;
    public ?string $banner_image_existing = null;
    public array $team_members = [];
    public array $team_uploads = [];

    // About
    public string $about_title = '';
    public string $about_subtitle = '';
    public string $about_badge = '';
    public $about_image;
    public ?string $about_image_existing = null;
    public array $about_progress = [];

    // Services
    public array $services = [];
    public array $service_uploads = [];

    // Services hero
    public string $services_hero_title = '';
    public string $services_hero_body = '';
    public string $services_hero_badge = '';
    public $services_hero_image;
    public ?string $services_hero_image_existing = null;

    // Programs
    public string $programs_hero_title = '';
    public string $programs_hero_body = '';
    public string $programs_hero_badge = '';
    public $programs_hero_image;
    public ?string $programs_hero_image_existing = null;

    // Projects
    public string $projects_hero_title = '';
    public string $projects_hero_body = '';
    public string $projects_hero_badge = '';
    public $projects_hero_image;
    public ?string $projects_hero_image_existing = null;

    // Posts
    public string $posts_hero_title = '';
    public string $posts_hero_body = '';
    public string $posts_hero_badge = '';
    public $posts_hero_image;
    public ?string $posts_hero_image_existing = null;

    // Contact
    public string $contact_hero_title = '';
    public string $contact_hero_body = '';
    public string $contact_hero_badge = '';
    public $contact_hero_image;
    public ?string $contact_hero_image_existing = null;

    // Shared Hero (image unique pour tous les heroes)
    public $shared_hero_image;
    public ?string $shared_hero_image_existing = null;

    // SEO
    public array $seo = [];
    public $seo_og_image;
    public ?string $seo_og_image_existing = null;

    // Theme
    public string $theme_primary = '#003366';
    public string $theme_accent = '#004080';

    // Global / Footer
    public string $company_name = '';
    public string $company_siret = '';
    public string $company_capital = '';
    public string $company_tva = '';
    public string $company_address = '';
    public string $company_phone = '';
    public string $company_email = '';
    public string $company_whatsapp = '';
    public string $company_hours = '';
    public string $footer_copyright = '';
    public string $footer_mentions_legales = '';
    public string $footer_cgv = '';
    public string $footer_confidentialite = '';
    public string $footer_securite = '';
    public array $social_networks = [];
    public array $footer_links = [];

    // Header / Navigation
    public $header_logo;
    public ?string $header_logo_existing = null;
    public array $menu_items = [];
    public string $header_phone = '';
    public string $header_email = '';
    public string $header_whatsapp = '';
    public string $header_cta_text = '';
    public string $header_cta_url = '';

    // Testimonials
    public array $testimonials = [];
    public array $testimonial_uploads = [];

    // Partners
    public array $partners = [];
    public array $partner_uploads = [];

    // Media Library
    public $media_upload;
    public array $media_library = [];
    public string $media_search = '';
    public array $media_picker = [];

    public ?string $successMsg = null;

    // Livewire upload error — remplace message générique "failed to upload" par aide concrète
    public function _uploadErrored($name, $errorsInJson, $isMultiple): void
    {
        $this->dispatch('upload:errored', name: $name)->self();
        $uploadMax = ini_get('upload_max_filesize') ?: '2M';
        $postMax = ini_get('post_max_size') ?: '8M';
        $msg = "Échec upload '{$name}' : limite serveur upload_max_filesize={$uploadMax} / post_max_size={$postMax} dépassée ou fichier non-image. Compressez l’image (<{$uploadMax}) en JPG/PNG/WebP et réessayez. Astuce: lancez `php -d upload_max_filesize=8M -d post_max_size=10M artisan serve`.";
        $this->addError($name, $msg);
        try { \Illuminate\Support\Facades\Log::warning("CMS upload failed {$name}: {$errorsInJson} | limit {$uploadMax}/{$postMax}"); } catch (\Throwable $e) {}
    }

    public function mount(): void
    {
        $this->authorize('cms.manage');

        $hero = SiteSetting::get('home.hero', []);
        $this->hero_badge = $hero['badge'] ?? '';
        $this->hero_title1 = $hero['title_line1'] ?? 'SIBEA-CI';
        $this->hero_title2 = $hero['title_line2'] ?? 'Bâtir l\'avenir';
        $this->hero_title3 = $hero['title_line3'] ?? 'en Côte d\'Ivoire';
        $this->hero_subtitle = $hero['subtitle'] ?? '';
        $this->cta_primary = $hero['cta_primary'] ?? 'NOS RÉALISATIONS';
        $this->cta_secondary = $hero['cta_secondary'] ?? 'DEMANDER UN DEVIS';
        $this->hero_slide1_existing = $hero['slide1_image'] ?? null;
        $this->hero_slide2_existing = $hero['slide2_image'] ?? null;

        $stats = SiteSetting::get('home.stats', []);
        $this->stat_projects = $stats['projects_completed'] ?? 1240;
        $this->stat_clients = $stats['happy_clients'] ?? 1750;
        $this->stat_workers = $stats['workers'] ?? 984;
        $this->stat_awards = $stats['awards'] ?? 96;
        $this->stat_surface = $stats['surface_total'] ?? 984000;

        $why = SiteSetting::get('home.why_choose', []);
        $this->why_title = $why['title'] ?? 'POURQUOI NOUS CHOISIR ?';
        $this->why_items = $why['items'] ?? [
            ['label' => 'Des équipes aux années d\'expérience', 'desc' => '30 ans cumulés, chefs de chantier certifiés.'],
            ['label' => 'Une qualité qui perdure après la livraison', 'desc' => 'SAV, garantie décennale.'],
            ['label' => 'Nous utilisons la technologie pour aller plus vite', 'desc' => 'BIM, drone, WebP.'],
            ['label' => 'Nos équipes formées en continu à la sécurité', 'desc' => 'RSE, EPI, normes ivoiriennes.'],
        ];

        $this->home_offers = SiteSetting::get('home.offers', ['RÉNOVATION','CONSEIL','CONSTRUCTION','ARCHITECTURE','ÉLECTRICITÉ']);
        $this->home_details = SiteSetting::get('home.details', [
            ['title' => 'Plomberie & VRD', 'desc' => 'VRD, assainissement, réseaux EU/EP — conforme normes ivoiriennes.'],
            ['title' => 'Peinture Murale', 'desc' => 'Finitions haut de gamme, peinture, revêtements, étanchéité.'],
            ['title' => 'Toiture Métallique', 'desc' => 'Charpente métallique, toiture bac acier, anti-corrosion côtière.'],
            ['title' => 'Préparation des Sols', 'desc' => 'Terrassement, plateforme, préparation sols avant construction.'],
        ]);
        $banner = SiteSetting::get('home.banner', []);
        $this->banner_title = $banner['title'] ?? 'Entrepreneurs & Conducteurs de travaux depuis 1981';
        $this->banner_cta_label = $banner['cta_label'] ?? 'DEMANDER UN DEVIS';
        $this->banner_cta_url = $banner['cta_url'] ?? '/contact';
        $this->banner_image_existing = $banner['image'] ?? null;
        $this->team_members = SiteSetting::get('home.team', [
            ['name' => 'Richard Wagner', 'role' => 'Ingénieur Civil', 'avatar' => null],
            ['name' => 'Sarah Spence', 'role' => 'Assistant Conducteur', 'avatar' => null],
            ['name' => 'John Halpern', 'role' => 'Conducteur de Travaux', 'avatar' => null],
            ['name' => 'Tommy Atkins', 'role' => 'Électriciens', 'avatar' => null],
        ]);

        $aboutHero = SiteSetting::get('about.hero', []);
        $this->about_title = $aboutHero['title'] ?? 'À propos — SIBEA-CI';
        $this->about_subtitle = $aboutHero['subtitle'] ?? $aboutHero['body'] ?? '';
        $this->about_badge = $aboutHero['badge'] ?? 'LABORATOIRE URBAIN • ABIDJAN 2020';
        $this->about_image_existing = $aboutHero['image'] ?? null;
        $this->about_progress = SiteSetting::get('about.progress', [
            ['label' => 'BTP & GÉNIE CIVIL', 'pct' => 95],
            ['label' => 'ÉLECTRICITÉ', 'pct' => 88],
            ['label' => 'PÉTROLE & ÉNERGIE', 'pct' => 85],
            ['label' => 'AGRO-INDUSTRIE', 'pct' => 90],
        ]);

        $this->services = SiteSetting::get('services.list', []);
        if (empty($this->services)) {
            $this->services = array_map(fn($c) => ['key' => $c->value, 'title' => $c->label(), 'desc' => '', 'image' => null], \App\Enums\ServiceType::cases());
        }

        $servicesHero = SiteSetting::get('services.hero', [
            'title' => '',
            'body' => '',
            'badge' => 'SERVICES — 6 EXPERTISES',
            'image' => null,
        ]);
        $this->services_hero_title = $servicesHero['title'] ?? '';
        $this->services_hero_body = $servicesHero['body'] ?? '';
        $this->services_hero_badge = $servicesHero['badge'] ?? '';
        $this->services_hero_image_existing = $servicesHero['image'] ?? null;

        $this->seo = SiteSetting::get('seo', [
            'home_title' => 'SIBEA-CI — BTP, Électricité, Pétrole, Agro-industrie',
            'home_desc' => 'SIBEA-CI Bingerville Abatta — BTP, électricité, pétrole, agro.',
            'about_title' => 'À propos — SIBEA-CI',
            'about_desc' => 'Entreprise ivoirienne BTP VRD lotissement.',
            'services_title' => 'Nos Services — BTP, VRD, Foncier',
            'services_desc' => '6 expertises ivoiriennes.',
        ]);
        $this->seo_og_image_existing = $this->seo['og_image'] ?? null;

        $theme = SiteSetting::get('theme', []);
        $this->theme_primary = $theme['primary'] ?? '#003366';
        $this->theme_accent = $theme['accent'] ?? '#004080';

        // Programs hero
        $programsHero = SiteSetting::get('programs.hero', [
            'title' => 'FONCIER — VIABILISATION',
            'body' => "Catalogue en temps réel — Disponible, Réservé, Vendu. Plans de masse PDF, ACD, viabilisation Bingerville Abatta. Réponses contextualisées.",
            'image' => null,
            'badge' => 'FONCIER — VIABILISATION',
        ]);
        $this->programs_hero_title = $programsHero['title'] ?? '';
        $this->programs_hero_body = $programsHero['body'] ?? '';
        $this->programs_hero_badge = $programsHero['badge'] ?? '';
        $this->programs_hero_image_existing = $programsHero['image'] ?? null;

        // Projects hero
        $projectsHero = SiteSetting::get('projects.hero', [
            'title' => 'Réalisations <span class="font-black">contextualisées</span>',
            'body' => 'Chaque projet est une réponse concrète et contextualisée — BTP, Électricité, Pétrole, Agro-industrie à Abidjan Bingerville. Filtrez par expertise et avancement.',
            'image' => null,
            'badge' => 'PORTFOLIO — 4 PÔLES',
        ]);
        $this->projects_hero_title = $projectsHero['title'] ?? '';
        $this->projects_hero_body = $projectsHero['body'] ?? '';
        $this->projects_hero_badge = $projectsHero['badge'] ?? '';
        $this->projects_hero_image_existing = $projectsHero['image'] ?? null;

        // Posts hero
        $postsHero = SiteSetting::get('posts.hero', [
            'title' => 'Actualités & <span class="font-black">Recherche</span>',
            'body' => 'Conseils foncier, normes BTP, suivi chantiers Bingerville — publications contextualisées du laboratoire SIBEA-CI.',
            'image' => null,
            'badge' => 'RECHERCHE — PUBLICATIONS',
        ]);
        $this->posts_hero_title = $postsHero['title'] ?? '';
        $this->posts_hero_body = $postsHero['body'] ?? '';
        $this->posts_hero_badge = $postsHero['badge'] ?? '';
        $this->posts_hero_image_existing = $postsHero['image'] ?? null;

        // Contact hero
        $contactHero = SiteSetting::get('contact.hero', [
            'title' => 'Contact & <span class="font-black">Devis</span>',
            'body' => 'Formulaire à étapes — BTP vs Foncier vs Pétrole vs Agro — validation Livewire, réponse sous 24h. Siège : Abidjan Bingerville Abatta Lot 935 Îlot 86, près Hôtel Blanc Cerf.',
            'image' => null,
            'badge' => 'LABORATOIRE — CONTACT ÉTUDE',
        ]);
        $this->contact_hero_title = $contactHero['title'] ?? '';
        $this->contact_hero_body = $contactHero['body'] ?? '';
        $this->contact_hero_badge = $contactHero['badge'] ?? '';
        $this->contact_hero_image_existing = $contactHero['image'] ?? null;

        // Shared Hero — image unique pour tous les heroes (fallback global)
        $sharedHero = SiteSetting::get('hero.shared', []);
        $this->shared_hero_image_existing = $sharedHero['image'] ?? null;

        // Global / Footer
        $global = SiteSetting::get('global', [
            'company_name' => 'SIBEA-CI',
            'company_siret' => 'CI-2022-0016466 Q',
            'company_capital' => '100 000 000 FCFA',
            'company_tva' => 'CI00123456789',
            'company_address' => 'Abidjan, Bingerville, Abatta (Lot 935, Îlot 86)',
            'company_phone' => '+225 07 00 00 00 00',
            'company_email' => 'contact@sibea-ci.ci',
            'company_whatsapp' => '+225 07 00 00 00 00',
            'company_hours' => 'Lun-Ven 8h-17h, Sam 8h-12h',
            'footer_copyright' => '© 2024 SIBEA-CI. Tous droits réservés.',
            'footer_mentions_legales' => 'Mentions légales',
            'footer_cgv' => 'Conditions Générales de Vente',
            'footer_confidentialite' => 'Politique de confidentialité',
            'social_networks' => [
                ['name' => 'Facebook', 'url' => 'https://facebook.com/sibea-ci', 'icon' => 'facebook'],
                ['name' => 'LinkedIn', 'url' => 'https://linkedin.com/company/sibea-ci', 'icon' => 'linkedin'],
                ['name' => 'Instagram', 'url' => 'https://instagram.com/sibea_ci', 'icon' => 'instagram'],
                ['name' => 'YouTube', 'url' => 'https://youtube.com/@sibea-ci', 'icon' => 'youtube'],
            ],
        ]);
        $this->company_name = $global['company_name'] ?? '';
        $this->company_siret = $global['company_siret'] ?? '';
        $this->company_capital = $global['company_capital'] ?? '';
        $this->company_tva = $global['company_tva'] ?? '';
        $this->company_address = $global['company_address'] ?? '';
        $this->company_phone = $global['company_phone'] ?? '';
        $this->company_email = $global['company_email'] ?? '';
        $this->company_whatsapp = $global['company_whatsapp'] ?? '';
        $this->company_hours = $global['company_hours'] ?? '';
        $this->footer_copyright = $global['footer_copyright'] ?? '';
        $this->footer_mentions_legales = $global['footer_mentions_legales'] ?? '';
        $this->footer_cgv = $global['footer_cgv'] ?? '';
        $this->footer_confidentialite = $global['footer_confidentialite'] ?? '';
        $this->footer_securite = $global['footer_securite'] ?? '';
        $this->social_networks = $global['social_networks'] ?? [];
        $this->footer_links = $global['footer_links'] ?? SiteSetting::get('footer.legal', []) ?? [
            ['label' => 'Accueil', 'url' => '/'],
            ['label' => 'À propos', 'url' => '/a-propos'],
            ['label' => 'Services', 'url' => '/services'],
            ['label' => 'Lotissements', 'url' => '/lotissements'],
            ['label' => 'Réalisations', 'url' => '/realisations'],
            ['label' => 'Actualités', 'url' => '/actualites'],
            ['label' => 'Contact', 'url' => '/contact'],
        ];

        // Header / Navigation
        $header = SiteSetting::get('header', [
            'menu_items' => [
                ['label' => 'Accueil', 'url' => '/', 'order' => 1],
                ['label' => 'À propos', 'url' => '/a-propos', 'order' => 2],
                ['label' => 'Services', 'url' => '/services', 'order' => 3],
                ['label' => 'Réalisations', 'url' => '/realisations', 'order' => 4],
                ['label' => 'Lotissements', 'url' => '/lotissements', 'order' => 5],
                ['label' => 'Actualités', 'url' => '/actualites', 'order' => 6],
                ['label' => 'Contact', 'url' => '/contact', 'order' => 7],
            ],
            'header_phone' => '+225 07 00 00 00 00',
            'header_email' => 'contact@sibea-ci.ci',
            'header_whatsapp' => '+225 07 00 00 00 00',
            'header_cta_text' => 'DEMANDER UN DEVIS',
            'header_cta_url' => '/contact',
        ]);
        $this->header_logo_existing = $header['logo'] ?? null;
        $this->menu_items = $header['menu_items'] ?? [];
        $this->header_phone = $header['header_phone'] ?? '';
        $this->header_email = $header['header_email'] ?? '';
        $this->header_whatsapp = $header['header_whatsapp'] ?? '';
        $this->header_cta_text = $header['header_cta_text'] ?? '';
        $this->header_cta_url = $header['header_cta_url'] ?? '';

        // Testimonials
        $this->testimonials = SiteSetting::get('testimonials', [
            ['name' => 'GEORGE SLOWS', 'role' => 'Chef de Chantier', 'content' => 'SIBEA-CI a livré notre villa dans les délais, avec un suivi VRD impeccable et un ACD sécurisé. Une équipe réactive et professionnelle.', 'rating' => 5, 'avatar' => null],
            ['name' => 'BARBARA DOUGLAS', 'role' => 'Chef de Chantier', 'content' => 'SIBEA-CI a livré notre villa dans les délais, avec un suivi VRD impeccable et un ACD sécurisé. Une équipe réactive et professionnelle.', 'rating' => 5, 'avatar' => null],
            ['name' => 'JOHN HALPERN', 'role' => 'Conducteur de Travaux', 'content' => 'SIBEA-CI a livré notre villa dans les délais, avec un suivi VRD impeccable et un ACD sécurisé. Une équipe réactive et professionnelle.', 'rating' => 5, 'avatar' => null],
        ]);

        // Partners
        $this->partners = SiteSetting::get('partners', [
            ['name' => 'NSIA Banque', 'url' => 'https://nsia.ci', 'logo' => null],
            ['name' => 'SIB', 'url' => 'https://sib.ci', 'logo' => null],
            ['name' => 'BOA CI', 'url' => 'https://boaci.ci', 'logo' => null],
            ['name' => 'SGCI', 'url' => 'https://sgci.ci', 'logo' => null],
            ['name' => 'BACI', 'url' => 'https://baci.ci', 'logo' => null],
        ]);

        // Media Library
        $this->media_library = SiteSetting::get('media_library', []);
    }

    public function saveHome(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'hero_badge' => ['nullable', 'string', 'max:255'],
            'hero_title1' => ['required', 'string', 'max:100'],
            'hero_title2' => ['required', 'string', 'max:100'],
            'hero_title3' => ['nullable', 'string', 'max:100'],
            'hero_subtitle' => ['nullable', 'string', 'max:500'],
            'cta_primary' => ['nullable', 'string', 'max:50'],
            'cta_secondary' => ['nullable', 'string', 'max:50'],
            'hero_slide1' => ['nullable', 'image', 'max:5120'],
            'hero_slide2' => ['nullable', 'image', 'max:5120'],
            'stat_projects' => ['required', 'integer', 'min:0'],
            'stat_clients' => ['required', 'integer', 'min:0'],
            'stat_workers' => ['required', 'integer', 'min:0'],
            'stat_awards' => ['required', 'integer', 'min:0'],
            'stat_surface' => ['required', 'integer', 'min:0'],
            'why_title' => ['nullable', 'string', 'max:100'],
            'why_items' => ['nullable', 'array', 'max:6'],
            'why_items.*.label' => ['required_with:why_items', 'string', 'max:100'],
            'why_items.*.desc' => ['nullable', 'string', 'max:500'],
        ]);

        $hero = SiteSetting::get('home.hero', []);
        if ($this->hero_slide1) {
            $hero['slide1_image'] = ImageService::storeOptimized($this->hero_slide1, 'cms/home', 'public', $hero['slide1_image'] ?? null);
            $this->hero_slide1_existing = $hero['slide1_image'];
            $this->hero_slide1 = null;
        }
        if ($this->hero_slide2) {
            $hero['slide2_image'] = ImageService::storeOptimized($this->hero_slide2, 'cms/home', 'public', $hero['slide2_image'] ?? null);
            $this->hero_slide2_existing = $hero['slide2_image'];
            $this->hero_slide2 = null;
        }
        $hero['badge'] = $validated['hero_badge'] ?? '';
        $hero['title_line1'] = $validated['hero_title1'];
        $hero['title_line2'] = $validated['hero_title2'];
        $hero['title_line3'] = $validated['hero_title3'] ?? '';
        $hero['subtitle'] = $validated['hero_subtitle'] ?? '';
        $hero['cta_primary'] = $validated['cta_primary'] ?? '';
        $hero['cta_secondary'] = $validated['cta_secondary'] ?? '';
        SiteSetting::set('home.hero', $hero, 'home');
        SiteSetting::set('home.stats', [
            'projects_completed' => $validated['stat_projects'],
            'happy_clients' => $validated['stat_clients'],
            'workers' => $validated['stat_workers'],
            'awards' => $validated['stat_awards'],
            'surface_total' => $validated['stat_surface'],
        ], 'home');
        SiteSetting::set('home.why_choose', ['title' => $validated['why_title'] ?? $this->why_title ?? 'POURQUOI NOUS CHOISIR ?', 'items' => $validated['why_items'] ?? []], 'home');
        $this->why_title = $validated['why_title'] ?? $this->why_title;
        $this->why_items = $validated['why_items'] ?? [];
        $this->successMsg = 'Accueil enregistré — vitrine mise à jour instantanément.';
        session()->flash('success', $this->successMsg);
        $this->dispatch('cms-saved');
    }

    public function removeHeroImage(string $which): void
    {
        $this->authorize('cms.manage');
        $hero = SiteSetting::get('home.hero', []);
        $key = $which === 'slide1' ? 'slide1_image' : 'slide2_image';
        if (!empty($hero[$key])) {
            ImageService::delete($hero[$key]);
            $hero[$key] = null;
            SiteSetting::set('home.hero', $hero, 'home');
            if ($which === 'slide1') $this->hero_slide1_existing = null;
            else $this->hero_slide2_existing = null;
            $this->successMsg = 'Image supprimée.';
        }
    }

    public function saveHomeOffers(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'home_offers' => ['required', 'array', 'min:1', 'max:10'],
            'home_offers.*' => ['required', 'string', 'max:50'],
            'home_details' => ['required', 'array', 'min:1', 'max:10'],
            'home_details.*.title' => ['required', 'string', 'max:100'],
            'home_details.*.desc' => ['nullable', 'string', 'max:500'],
        ]);
        SiteSetting::set('home.offers', $validated['home_offers'], 'home');
        SiteSetting::set('home.details', $validated['home_details'], 'home');
        $this->successMsg = 'Offres & Détails enregistrés.';
        session()->flash('success', $this->successMsg);
    }

    public function addHomeOffer(): void { $this->home_offers[] = 'Nouvelle offre'; }
    public function removeHomeOffer(int $idx): void { unset($this->home_offers[$idx]); $this->home_offers = array_values($this->home_offers); }
    public function addHomeDetail(): void { $this->home_details[] = ['title' => 'Nouveau', 'desc' => '']; }
    public function removeHomeDetail(int $idx): void { unset($this->home_details[$idx]); $this->home_details = array_values($this->home_details); }

    public function saveBanner(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'banner_title' => ['required', 'string', 'max:255'],
            'banner_cta_label' => ['nullable', 'string', 'max:50'],
            'banner_cta_url' => ['nullable', 'string', 'max:255'],
            'banner_image' => ['nullable', 'image', 'max:5120'],
        ]);
        $banner = SiteSetting::get('home.banner', []);
        if ($this->banner_image) {
            $banner['image'] = ImageService::storeOptimized($this->banner_image, 'cms/home', 'public', $banner['image'] ?? null);
            $this->banner_image_existing = $banner['image'];
            $this->banner_image = null;
        }
        $banner['title'] = $validated['banner_title'];
        $banner['cta_label'] = $validated['banner_cta_label'] ?? '';
        $banner['cta_url'] = $validated['banner_cta_url'] ?? '';
        SiteSetting::set('home.banner', $banner, 'home');
        $this->successMsg = 'Bannière enregistrée.';
        session()->flash('success', $this->successMsg);
    }

    public function removeBannerImage(): void
    {
        $this->authorize('cms.manage');
        $banner = SiteSetting::get('home.banner', []);
        if (!empty($banner['image'])) {
            ImageService::delete($banner['image']);
            $banner['image'] = null;
            SiteSetting::set('home.banner', $banner, 'home');
            $this->banner_image_existing = null;
            $this->successMsg = 'Image bannière supprimée.';
        }
    }

    public function saveTeam(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'team_members' => ['required', 'array', 'min:1', 'max:12'],
            'team_members.*.name' => ['required', 'string', 'max:100'],
            'team_members.*.role' => ['nullable', 'string', 'max:100'],
            'team_uploads.*' => ['nullable', 'image', 'max:5120'],
        ]);
        foreach ($this->team_uploads as $idx => $file) {
            if ($file) {
                $old = $this->team_members[$idx]['avatar'] ?? null;
                $validated['team_members'][$idx]['avatar'] = ImageService::storeOptimized($file, 'cms/team', 'public', $old);
            } else {
                $validated['team_members'][$idx]['avatar'] = $this->team_members[$idx]['avatar'] ?? null;
            }
        }
        foreach ($validated['team_members'] as $i => $m) {
            if (!isset($m['avatar'])) $validated['team_members'][$i]['avatar'] = $this->team_members[$i]['avatar'] ?? null;
        }
        SiteSetting::set('home.team', $validated['team_members'], 'home');
        $this->team_members = $validated['team_members'];
        $this->team_uploads = [];
        $this->successMsg = 'Équipe enregistrée.';
        session()->flash('success', $this->successMsg);
    }

    public function addTeamMember(): void { $this->team_members[] = ['name' => '', 'role' => '', 'avatar' => null]; }
    public function removeTeamMember(int $idx): void { $old = $this->team_members[$idx]['avatar'] ?? null; unset($this->team_members[$idx]); $this->team_members = array_values($this->team_members); $new = []; foreach ($this->team_uploads as $k=>$v){ if($k<$idx) $new[$k]=$v; elseif($k>$idx) $new[$k-1]=$v; } $this->team_uploads=$new; if($old) ImageService::delete($old); }
    public function moveTeamUp(int $idx): void { if($idx<=0||!isset($this->team_members[$idx])) return; $tmp=$this->team_members[$idx-1]; $this->team_members[$idx-1]=$this->team_members[$idx]; $this->team_members[$idx]=$tmp; $tmpUp=$this->team_uploads[$idx]??null; $prev=$this->team_uploads[$idx-1]??null; if($prev===null) unset($this->team_uploads[$idx]); else $this->team_uploads[$idx]=$prev; if($tmpUp===null) unset($this->team_uploads[$idx-1]); else $this->team_uploads[$idx-1]=$tmpUp; }
    public function moveTeamDown(int $idx): void { if($idx>=count($this->team_members)-1) return; $tmp=$this->team_members[$idx+1]; $this->team_members[$idx+1]=$this->team_members[$idx]; $this->team_members[$idx]=$tmp; $tmpUp=$this->team_uploads[$idx]??null; $next=$this->team_uploads[$idx+1]??null; if($next===null) unset($this->team_uploads[$idx]); else $this->team_uploads[$idx]=$next; if($tmpUp===null) unset($this->team_uploads[$idx+1]); else $this->team_uploads[$idx+1]=$tmpUp; }
    public function removeTeamAvatar(int $idx): void { $img=$this->team_members[$idx]['avatar']??null; if($img){ ImageService::delete($img); $this->team_members[$idx]['avatar']=null; SiteSetting::set('home.team',$this->team_members,'home'); $this->successMsg='Avatar supprimé.'; } }

    public function saveAbout(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'about_title' => ['required', 'string', 'max:255'],
            'about_subtitle' => ['nullable', 'string', 'max:1000'],
            'about_badge' => ['nullable', 'string', 'max:255'],
            'about_image' => ['nullable', 'image', 'max:5120'],
            'about_progress' => ['required', 'array', 'size:4'],
            'about_progress.*.label' => ['required', 'string', 'max:100'],
            'about_progress.*.pct' => ['required', 'integer', 'min:0', 'max:100'],
        ]);
        $hero = SiteSetting::get('about.hero', []);
        if ($this->about_image) {
            $hero['image'] = ImageService::storeOptimized($this->about_image, 'cms/about', 'public', $hero['image'] ?? null);
            $this->about_image_existing = $hero['image'];
            $this->about_image = null;
        }
        $hero['title'] = $validated['about_title'];
        $hero['subtitle'] = $validated['about_subtitle'] ?? '';
        $hero['body'] = $validated['about_subtitle'] ?? '';
        $hero['badge'] = $validated['about_badge'] ?? $this->about_badge ?? '';
        SiteSetting::set('about.hero', $hero, 'about');
        SiteSetting::set('about.progress', $validated['about_progress'], 'about');
        $this->successMsg = 'À propos enregistré.';
        session()->flash('success', $this->successMsg);
    }

    public function removeAboutImage(): void
    {
        $this->authorize('cms.manage');
        $hero = SiteSetting::get('about.hero', []);
        if (!empty($hero['image'])) {
            ImageService::delete($hero['image']);
            $hero['image'] = null;
            SiteSetting::set('about.hero', $hero, 'about');
            $this->about_image_existing = null;
            $this->successMsg = 'Image À propos supprimée.';
        }
    }

    public function saveServicesHero(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'services_hero_title' => ['nullable', 'string', 'max:255'],
            'services_hero_body' => ['nullable', 'string', 'max:1000'],
            'services_hero_badge' => ['nullable', 'string', 'max:255'],
            'services_hero_image' => ['nullable', 'image', 'max:5120'],
        ]);
        $hero = SiteSetting::get('services.hero', []);
        if ($this->services_hero_image) {
            $hero['image'] = ImageService::storeOptimized($this->services_hero_image, 'cms/services', 'public', $hero['image'] ?? null);
            $this->services_hero_image_existing = $hero['image'];
            $this->services_hero_image = null;
        }
        $hero['title'] = $validated['services_hero_title'] ?? '';
        $hero['body'] = $validated['services_hero_body'] ?? '';
        $hero['badge'] = $validated['services_hero_badge'] ?? '';
        SiteSetting::set('services.hero', $hero, 'services');
        $this->successMsg = 'Hero Services enregistré.';
        session()->flash('success', $this->successMsg);
    }

    public function removeServicesHeroImage(): void
    {
        $this->authorize('cms.manage');
        $hero = SiteSetting::get('services.hero', []);
        if (!empty($hero['image'])) {
            ImageService::delete($hero['image']);
            $hero['image'] = null;
            SiteSetting::set('services.hero', $hero, 'services');
            $this->services_hero_image_existing = null;
            $this->successMsg = 'Image hero Services supprimée.';
        }
    }

    public function savePrograms(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'programs_hero_title' => ['required', 'string', 'max:255'],
            'programs_hero_body' => ['nullable', 'string', 'max:1000'],
            'programs_hero_badge' => ['nullable', 'string', 'max:255'],
            'programs_hero_image' => ['nullable', 'image', 'max:5120'],
        ]);
        $hero = SiteSetting::get('programs.hero', []);
        if ($this->programs_hero_image) {
            $hero['image'] = ImageService::storeOptimized($this->programs_hero_image, 'cms/programs', 'public', $hero['image'] ?? null);
            $this->programs_hero_image_existing = $hero['image'];
            $this->programs_hero_image = null;
        }
        $hero['title'] = $validated['programs_hero_title'];
        $hero['body'] = $validated['programs_hero_body'] ?? '';
        $hero['badge'] = $validated['programs_hero_badge'] ?? '';
        SiteSetting::set('programs.hero', $hero, 'programs');
        $this->successMsg = 'Programmes (Lotissements) enregistrés.';
        session()->flash('success', $this->successMsg);
    }

    public function removeProgramsImage(): void
    {
        $this->authorize('cms.manage');
        $hero = SiteSetting::get('programs.hero', []);
        if (!empty($hero['image'])) {
            ImageService::delete($hero['image']);
            $hero['image'] = null;
            SiteSetting::set('programs.hero', $hero, 'programs');
            $this->programs_hero_image_existing = null;
            $this->successMsg = 'Image Programmes supprimée.';
        }
    }

    public function saveProjects(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'projects_hero_title' => ['required', 'string', 'max:255'],
            'projects_hero_body' => ['nullable', 'string', 'max:1000'],
            'projects_hero_badge' => ['nullable', 'string', 'max:255'],
            'projects_hero_image' => ['nullable', 'image', 'max:5120'],
        ]);
        $hero = SiteSetting::get('projects.hero', []);
        if ($this->projects_hero_image) {
            $hero['image'] = ImageService::storeOptimized($this->projects_hero_image, 'cms/projects', 'public', $hero['image'] ?? null);
            $this->projects_hero_image_existing = $hero['image'];
            $this->projects_hero_image = null;
        }
        $hero['title'] = $validated['projects_hero_title'];
        $hero['body'] = $validated['projects_hero_body'] ?? '';
        $hero['badge'] = $validated['projects_hero_badge'] ?? '';
        SiteSetting::set('projects.hero', $hero, 'projects');
        $this->successMsg = 'Projets (Réalisations) enregistrés.';
        session()->flash('success', $this->successMsg);
    }

    public function removeProjectsImage(): void
    {
        $this->authorize('cms.manage');
        $hero = SiteSetting::get('projects.hero', []);
        if (!empty($hero['image'])) {
            ImageService::delete($hero['image']);
            $hero['image'] = null;
            SiteSetting::set('projects.hero', $hero, 'projects');
            $this->projects_hero_image_existing = null;
            $this->successMsg = 'Image Projets supprimée.';
        }
    }

    public function savePosts(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'posts_hero_title' => ['required', 'string', 'max:255'],
            'posts_hero_body' => ['nullable', 'string', 'max:1000'],
            'posts_hero_badge' => ['nullable', 'string', 'max:255'],
            'posts_hero_image' => ['nullable', 'image', 'max:5120'],
        ]);
        $hero = SiteSetting::get('posts.hero', []);
        if ($this->posts_hero_image) {
            $hero['image'] = ImageService::storeOptimized($this->posts_hero_image, 'cms/posts', 'public', $hero['image'] ?? null);
            $this->posts_hero_image_existing = $hero['image'];
            $this->posts_hero_image = null;
        }
        $hero['title'] = $validated['posts_hero_title'];
        $hero['body'] = $validated['posts_hero_body'] ?? '';
        $hero['badge'] = $validated['posts_hero_badge'] ?? '';
        SiteSetting::set('posts.hero', $hero, 'posts');
        $this->successMsg = 'Actualités enregistrées.';
        session()->flash('success', $this->successMsg);
    }

    public function removePostsImage(): void
    {
        $this->authorize('cms.manage');
        $hero = SiteSetting::get('posts.hero', []);
        if (!empty($hero['image'])) {
            ImageService::delete($hero['image']);
            $hero['image'] = null;
            SiteSetting::set('posts.hero', $hero, 'posts');
            $this->posts_hero_image_existing = null;
            $this->successMsg = 'Image Actualités supprimée.';
        }
    }

    public function saveContact(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'contact_hero_title' => ['required', 'string', 'max:255'],
            'contact_hero_body' => ['nullable', 'string', 'max:1000'],
            'contact_hero_badge' => ['nullable', 'string', 'max:255'],
            'contact_hero_image' => ['nullable', 'image', 'max:5120'],
        ]);
        $hero = SiteSetting::get('contact.hero', []);
        if ($this->contact_hero_image) {
            $hero['image'] = ImageService::storeOptimized($this->contact_hero_image, 'cms/contact', 'public', $hero['image'] ?? null);
            $this->contact_hero_image_existing = $hero['image'];
            $this->contact_hero_image = null;
        }
        $hero['title'] = $validated['contact_hero_title'];
        $hero['body'] = $validated['contact_hero_body'] ?? '';
        $hero['badge'] = $validated['contact_hero_badge'] ?? '';
        SiteSetting::set('contact.hero', $hero, 'contact');
        $this->successMsg = 'Contact enregistré.';
        session()->flash('success', $this->successMsg);
    }

    public function removeContactImage(): void
    {
        $this->authorize('cms.manage');
        $hero = SiteSetting::get('contact.hero', []);
        if (!empty($hero['image'])) {
            ImageService::delete($hero['image']);
            $hero['image'] = null;
            SiteSetting::set('contact.hero', $hero, 'contact');
            $this->contact_hero_image_existing = null;
            $this->successMsg = 'Image Contact supprimée.';
        }
    }

    public function saveSharedHero(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'shared_hero_image' => ['nullable', 'image', 'max:5120'],
        ]);
        $hero = SiteSetting::get('hero.shared', []);
        if ($this->shared_hero_image) {
            $hero['image'] = ImageService::storeOptimized($this->shared_hero_image, 'cms/hero', 'public', $hero['image'] ?? null);
            $this->shared_hero_image_existing = $hero['image'];
            $this->shared_hero_image = null;
        }
        SiteSetting::set('hero.shared', $hero, 'hero');
        $this->successMsg = 'Image hero partagée enregistrée — tous les heroes utilisent maintenant cette image.';
        session()->flash('success', $this->successMsg);
    }

    public function removeSharedHeroImage(): void
    {
        $this->authorize('cms.manage');
        $hero = SiteSetting::get('hero.shared', []);
        if (!empty($hero['image'])) {
            ImageService::delete($hero['image']);
            $hero['image'] = null;
            SiteSetting::set('hero.shared', $hero, 'hero');
            $this->shared_hero_image_existing = null;
            $this->successMsg = 'Image hero partagée supprimée — retour aux images par page.';
        }
    }

    public function saveServices(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'services' => ['required', 'array', 'min:1', 'max:12'],
            'services.*.key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9\-]+$/'],
            'services.*.title' => ['required', 'string', 'max:100'],
            'services.*.desc' => ['nullable', 'string', 'max:500'],
            'service_uploads.*' => ['nullable', 'image', 'max:5120'],
        ], [
            'services.*.key.regex' => 'Clé: minuscules, chiffres et tirets uniquement.',
        ]);
        // Check uniqueness of keys
        $keys = array_column($validated['services'], 'key');
        if (count($keys) !== count(array_unique($keys))) {
            $this->addError('services', 'Les clés (slugs) doivent être uniques.');
            return;
        }
        foreach ($this->service_uploads as $idx => $file) {
            if ($file) {
                $old = $this->services[$idx]['image'] ?? null;
                $validated['services'][$idx]['image'] = ImageService::storeOptimized($file, 'cms/services', 'public', $old);
            } else {
                $validated['services'][$idx]['image'] = $this->services[$idx]['image'] ?? null;
            }
        }
        foreach ($validated['services'] as $i => $svc) {
            if (!isset($svc['image'])) $validated['services'][$i]['image'] = $this->services[$i]['image'] ?? null;
        }
        SiteSetting::set('services.list', $validated['services'], 'services');
        $this->services = $validated['services'];
        $this->service_uploads = [];
        $this->successMsg = 'Services enregistrés — vitrine instantanée.';
        session()->flash('success', $this->successMsg);
    }

    public function addService(): void
    {
        $this->services[] = ['key' => 'service-'.(count($this->services)+1), 'title' => 'Nouveau service', 'desc' => '', 'image' => null];
        $this->page = $this->totalPages;
    }

    public function removeService(int $idx): void
    {
        $oldImg = $this->services[$idx]['image'] ?? null;
        unset($this->services[$idx]);
        $this->services = array_values($this->services);
        $newUploads = [];
        foreach ($this->service_uploads as $k => $v) {
            if ($k < $idx) $newUploads[$k] = $v;
            elseif ($k > $idx) $newUploads[$k-1] = $v;
        }
        $this->service_uploads = $newUploads;
        if ($oldImg) ImageService::delete($oldImg);
    }

    public function moveServiceUp(int $idx): void
    {
        if ($idx <= 0 || !isset($this->services[$idx])) return;
        $tmp = $this->services[$idx-1];
        $this->services[$idx-1] = $this->services[$idx];
        $this->services[$idx] = $tmp;
        $tmpUp = $this->service_uploads[$idx] ?? null;
        $prev = $this->service_uploads[$idx-1] ?? null;
        if ($prev === null) unset($this->service_uploads[$idx]);
        else $this->service_uploads[$idx] = $prev;
        if ($tmpUp === null) unset($this->service_uploads[$idx-1]);
        else $this->service_uploads[$idx-1] = $tmpUp;
    }

    public function moveServiceDown(int $idx): void
    {
        if ($idx >= count($this->services)-1) return;
        $tmp = $this->services[$idx+1];
        $this->services[$idx+1] = $this->services[$idx];
        $this->services[$idx] = $tmp;
        $tmpUp = $this->service_uploads[$idx] ?? null;
        $next = $this->service_uploads[$idx+1] ?? null;
        if ($next === null) unset($this->service_uploads[$idx]);
        else $this->service_uploads[$idx] = $next;
        if ($tmpUp === null) unset($this->service_uploads[$idx+1]);
        else $this->service_uploads[$idx+1] = $tmpUp;
    }

    public function removeServiceImage(int $idx): void
    {
        $img = $this->services[$idx]['image'] ?? null;
        if ($img) {
            ImageService::delete($img);
            $this->services[$idx]['image'] = null;
            SiteSetting::set('services.list', $this->services, 'services');
            $this->successMsg = 'Image service supprimée.';
        }
    }

    public function saveSeo(): void
    {
        if (!auth()->user() || (!auth()->user()->can('cms.manage') && !auth()->user()->can('seo.manage'))) abort(403);
        $validated = $this->validate([
            // Meta tags
            'seo.home_title' => ['required', 'string', 'max:70'],
            'seo.home_desc' => ['nullable', 'string', 'max:160'],
            'seo.about_title' => ['nullable', 'string', 'max:70'],
            'seo.about_desc' => ['nullable', 'string', 'max:160'],
            'seo.services_title' => ['nullable', 'string', 'max:70'],
            'seo.services_desc' => ['nullable', 'string', 'max:160'],
            'seo.programs_title' => ['nullable', 'string', 'max:70'],
            'seo.programs_desc' => ['nullable', 'string', 'max:160'],
            'seo.projects_title' => ['nullable', 'string', 'max:70'],
            'seo.projects_desc' => ['nullable', 'string', 'max:160'],
            'seo.posts_title' => ['nullable', 'string', 'max:70'],
            'seo.posts_desc' => ['nullable', 'string', 'max:160'],
            'seo.contact_title' => ['nullable', 'string', 'max:70'],
            'seo.contact_desc' => ['nullable', 'string', 'max:160'],
            // Open Graph
            'seo.og_title' => ['nullable', 'string', 'max:95'],
            'seo.og_description' => ['nullable', 'string', 'max:200'],
            'seo.og_type' => ['nullable', 'string', 'max:50'],
            'seo.twitter_card' => ['nullable', 'string', 'max:20'],
            'seo.twitter_site' => ['nullable', 'string', 'max:50'],
            'seo.robots' => ['nullable', 'string', 'max:50'],
            'seo_og_image' => ['nullable', 'image', 'max:5120'],
        ]);

        $existing = SiteSetting::get('seo', []);
        $seoData = $validated['seo'] ?? [];
        if ($this->seo_og_image) {
            $seoData['og_image'] = ImageService::storeOptimized($this->seo_og_image, 'cms/seo', 'public', $existing['og_image'] ?? null);
            $this->seo_og_image = null;
            $this->seo_og_image_existing = $seoData['og_image'];
        }
        $merged = array_merge($existing, $seoData);
        SiteSetting::set('seo', $merged, 'seo');
        $this->seo = $merged;
        $this->successMsg = 'SEO & Open Graph enregistrés.';
        session()->flash('success', $this->successMsg);
    }

    public function saveTheme(): void
    {
        if (!auth()->user() || (!auth()->user()->can('cms.manage') && !auth()->user()->can('theme.manage'))) abort(403);
        $validated = $this->validate([
            'theme_primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme_accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
        SiteSetting::set('theme', ['primary' => $validated['theme_primary'], 'accent' => $validated['theme_accent']], 'theme');
        $this->successMsg = 'Thème enregistré — couleurs mises à jour.';
        session()->flash('success', $this->successMsg);
    }

    public function saveGlobal(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'company_siret' => ['nullable', 'string', 'max:50'],
            'company_capital' => ['nullable', 'string', 'max:50'],
            'company_tva' => ['nullable', 'string', 'max:50'],
            'company_address' => ['required', 'string', 'max:255'],
            'company_phone' => ['required', 'string', 'max:30'],
            'company_email' => ['required', 'email', 'max:100'],
            'company_whatsapp' => ['nullable', 'string', 'max:30'],
            'company_hours' => ['nullable', 'string', 'max:100'],
            'footer_copyright' => ['nullable', 'string', 'max:255'],
            'footer_mentions_legales' => ['nullable', 'string', 'max:100'],
            'footer_cgv' => ['nullable', 'string', 'max:100'],
            'footer_confidentialite' => ['nullable', 'string', 'max:100'],
            'social_networks' => ['nullable', 'array', 'max:10'],
            'social_networks.*.name' => ['required_with:social_networks', 'string', 'max:50'],
            'social_networks.*.url' => ['required_with:social_networks', 'url', 'max:255'],
            'social_networks.*.icon' => ['required_with:social_networks', 'string', 'max:50'],
            'footer_links' => ['nullable', 'array', 'max:15'],
            'footer_links.*.label' => ['required_with:footer_links', 'string', 'max:50'],
            'footer_links.*.url' => ['required_with:footer_links', 'string', 'max:255'],
        ]);

        $global = SiteSetting::get('global', []);
        $global = array_merge($global, $validated);
        // Persist légal links also in dedicated key for footer component
        if (isset($validated['footer_links'])) {
            SiteSetting::set('footer.legal', $validated['footer_links'], 'footer');
        }
        SiteSetting::set('global', $global, 'global');
        $this->successMsg = 'Infos globales / Footer enregistrées.';
        session()->flash('success', $this->successMsg);
    }

    public function removeGlobalImage(): void
    {
        $this->authorize('cms.manage');
        $global = SiteSetting::get('global', []);
        if (!empty($global['logo'])) {
            ImageService::delete($global['logo']);
            $global['logo'] = null;
            SiteSetting::set('global', $global, 'global');
            $this->successMsg = 'Logo global supprimé.';
        }
    }

    public function saveHeader(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'menu_items' => ['required', 'array', 'min:1', 'max:15'],
            'menu_items.*.label' => ['required', 'string', 'max:50'],
            'menu_items.*.url' => ['required', 'string', 'max:255'],
            'menu_items.*.order' => ['required', 'integer', 'min:1'],
            'header_phone' => ['nullable', 'string', 'max:30'],
            'header_email' => ['nullable', 'email', 'max:100'],
            'header_whatsapp' => ['nullable', 'string', 'max:30'],
            'header_cta_text' => ['nullable', 'string', 'max:50'],
            'header_cta_url' => ['nullable', 'string', 'max:255'],
            'header_logo' => ['nullable', 'image', 'max:5120'],
        ]);

        // Check uniqueness of order
        $orders = array_column($validated['menu_items'], 'order');
        if (count($orders) !== count(array_unique($orders))) {
            $this->addError('menu_items', 'Les ordres doivent être uniques.');
            return;
        }

        $header = SiteSetting::get('header', []);
        if ($this->header_logo) {
            $header['logo'] = ImageService::storeOptimized($this->header_logo, 'cms/header', 'public', $header['logo'] ?? null);
            $this->header_logo_existing = $header['logo'];
            $this->header_logo = null;
        }
        $header['menu_items'] = $validated['menu_items'];
        $header['header_phone'] = $validated['header_phone'] ?? '';
        $header['header_email'] = $validated['header_email'] ?? '';
        $header['header_whatsapp'] = $validated['header_whatsapp'] ?? '';
        $header['header_cta_text'] = $validated['header_cta_text'] ?? '';
        $header['header_cta_url'] = $validated['header_cta_url'] ?? '';
        SiteSetting::set('header', $header, 'header');
        $this->menu_items = $validated['menu_items'];
        $this->header_phone = $validated['header_phone'] ?? '';
        $this->header_email = $validated['header_email'] ?? '';
        $this->header_whatsapp = $validated['header_whatsapp'] ?? '';
        $this->header_cta_text = $validated['header_cta_text'] ?? '';
        $this->header_cta_url = $validated['header_cta_url'] ?? '';
        $this->successMsg = 'Header / Navigation enregistrés.';
        session()->flash('success', $this->successMsg);
    }

    public function removeHeaderLogo(): void
    {
        $this->authorize('cms.manage');
        $header = SiteSetting::get('header', []);
        if (!empty($header['logo'])) {
            ImageService::delete($header['logo']);
            $header['logo'] = null;
            SiteSetting::set('header', $header, 'header');
            $this->header_logo_existing = null;
            $this->successMsg = 'Logo header supprimé.';
        }
    }

    public function addMenuItem(): void
    {
        $maxOrder = 1;
        foreach ($this->menu_items as $item) {
            if (($item['order'] ?? 0) > $maxOrder) {
                $maxOrder = $item['order'];
            }
        }
        $this->menu_items[] = [
            'label' => 'Nouveau lien',
            'url' => '#',
            'order' => $maxOrder + 1,
        ];
    }

    public function removeMenuItem(int $idx): void
    {
        unset($this->menu_items[$idx]);
        $this->menu_items = array_values($this->menu_items);
    }

    public function moveMenuItemUp(int $idx): void
    {
        if ($idx <= 0 || !isset($this->menu_items[$idx])) return;
        $tmp = $this->menu_items[$idx-1];
        $this->menu_items[$idx-1] = $this->menu_items[$idx];
        $this->menu_items[$idx] = $tmp;
    }

    public function moveMenuItemDown(int $idx): void
    {
        if ($idx >= count($this->menu_items)-1) return;
        $tmp = $this->menu_items[$idx+1];
        $this->menu_items[$idx+1] = $this->menu_items[$idx];
        $this->menu_items[$idx] = $tmp;
    }

    public function saveTestimonials(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'testimonials' => ['required', 'array', 'max:12'],
            'testimonials.*.name' => ['required', 'string', 'max:100'],
            'testimonials.*.role' => ['required', 'string', 'max:100'],
            'testimonials.*.content' => ['required', 'string', 'max:1000'],
            'testimonials.*.rating' => ['required', 'integer', 'min:1', 'max:5'],
            'testimonial_uploads.*' => ['nullable', 'image', 'max:5120'],
        ]);

        foreach ($this->testimonial_uploads as $idx => $file) {
            if ($file) {
                $old = $this->testimonials[$idx]['avatar'] ?? null;
                $validated['testimonials'][$idx]['avatar'] = ImageService::storeOptimized($file, 'cms/testimonials', 'public', $old);
            } else {
                $validated['testimonials'][$idx]['avatar'] = $this->testimonials[$idx]['avatar'] ?? null;
            }
        }

        foreach ($validated['testimonials'] as $i => $t) {
            if (!isset($t['avatar'])) {
                $validated['testimonials'][$i]['avatar'] = $this->testimonials[$i]['avatar'] ?? null;
            }
        }

        SiteSetting::set('testimonials', $validated['testimonials'], 'testimonials');
        $this->testimonials = $validated['testimonials'];
        $this->testimonial_uploads = [];
        $this->successMsg = 'Témoignages enregistrés.';
        session()->flash('success', $this->successMsg);
    }

    public function addTestimonial(): void
    {
        $this->testimonials[] = ['name' => '', 'role' => '', 'content' => '', 'rating' => 5, 'avatar' => null];
        $this->page = $this->totalTestimonialPages;
    }

    public function removeTestimonial(int $idx): void
    {
        $oldAvatar = $this->testimonials[$idx]['avatar'] ?? null;
        unset($this->testimonials[$idx]);
        $this->testimonials = array_values($this->testimonials);
        $newUploads = [];
        foreach ($this->testimonial_uploads as $k => $v) {
            if ($k < $idx) $newUploads[$k] = $v;
            elseif ($k > $idx) $newUploads[$k-1] = $v;
        }
        $this->testimonial_uploads = $newUploads;
        if ($oldAvatar) ImageService::delete($oldAvatar);
    }

    public function moveTestimonialUp(int $idx): void
    {
        if ($idx <= 0 || !isset($this->testimonials[$idx])) return;
        $tmp = $this->testimonials[$idx-1];
        $this->testimonials[$idx-1] = $this->testimonials[$idx];
        $this->testimonials[$idx] = $tmp;
        $tmpUp = $this->testimonial_uploads[$idx] ?? null;
        $prev = $this->testimonial_uploads[$idx-1] ?? null;
        if ($prev === null) unset($this->testimonial_uploads[$idx]);
        else $this->testimonial_uploads[$idx] = $prev;
        if ($tmpUp === null) unset($this->testimonial_uploads[$idx-1]);
        else $this->testimonial_uploads[$idx-1] = $tmpUp;
    }

    public function moveTestimonialDown(int $idx): void
    {
        if ($idx >= count($this->testimonials)-1) return;
        $tmp = $this->testimonials[$idx+1];
        $this->testimonials[$idx+1] = $this->testimonials[$idx];
        $this->testimonials[$idx] = $tmp;
        $tmpUp = $this->testimonial_uploads[$idx] ?? null;
        $next = $this->testimonial_uploads[$idx+1] ?? null;
        if ($next === null) unset($this->testimonial_uploads[$idx]);
        else $this->testimonial_uploads[$idx] = $next;
        if ($tmpUp === null) unset($this->testimonial_uploads[$idx+1]);
        else $this->testimonial_uploads[$idx+1] = $tmpUp;
    }

    public function removeTestimonialImage(int $idx): void
    {
        $img = $this->testimonials[$idx]['avatar'] ?? null;
        if ($img) {
            ImageService::delete($img);
            $this->testimonials[$idx]['avatar'] = null;
            SiteSetting::set('testimonials', $this->testimonials, 'testimonials');
            $this->successMsg = 'Avatar témoignage supprimé.';
        }
    }

    public function savePartners(): void
    {
        $this->authorize('cms.manage');
        $validated = $this->validate([
            'partners' => ['required', 'array', 'min:1', 'max:20'],
            'partners.*.name' => ['required', 'string', 'max:100'],
            'partners.*.url' => ['nullable', 'url', 'max:255'],
            'partner_uploads.*' => ['nullable', 'image', 'max:5120'],
        ]);

        foreach ($this->partner_uploads as $idx => $file) {
            if ($file) {
                $old = $this->partners[$idx]['logo'] ?? null;
                $validated['partners'][$idx]['logo'] = ImageService::storeOptimized($file, 'cms/partners', 'public', $old);
            } else {
                $validated['partners'][$idx]['logo'] = $this->partners[$idx]['logo'] ?? null;
            }
        }

        foreach ($validated['partners'] as $i => $p) {
            if (!isset($p['logo'])) $validated['partners'][$i]['logo'] = $this->partners[$i]['logo'] ?? null;
        }

        SiteSetting::set('partners', $validated['partners'], 'partners');
        $this->partners = $validated['partners'];
        $this->partner_uploads = [];
        $this->successMsg = 'Partenaires enregistrés.';
        session()->flash('success', $this->successMsg);
    }

    public function addPartner(): void
    {
        $this->partners[] = ['name' => '', 'url' => '', 'logo' => null];
        $this->page = $this->totalPartnerPages;
    }

    public function removePartner(int $idx): void
    {
        $oldLogo = $this->partners[$idx]['logo'] ?? null;
        unset($this->partners[$idx]);
        $this->partners = array_values($this->partners);
        $newUploads = [];
        foreach ($this->partner_uploads as $k => $v) {
            if ($k < $idx) $newUploads[$k] = $v;
            elseif ($k > $idx) $newUploads[$k-1] = $v;
        }
        $this->partner_uploads = $newUploads;
        if ($oldLogo) ImageService::delete($oldLogo);
    }

    public function movePartnerUp(int $idx): void
    {
        if ($idx <= 0 || !isset($this->partners[$idx])) return;
        $tmp = $this->partners[$idx-1];
        $this->partners[$idx-1] = $this->partners[$idx];
        $this->partners[$idx] = $tmp;
        $tmpUp = $this->partner_uploads[$idx] ?? null;
        $prev = $this->partner_uploads[$idx-1] ?? null;
        if ($prev === null) unset($this->partner_uploads[$idx]);
        else $this->partner_uploads[$idx] = $prev;
        if ($tmpUp === null) unset($this->partner_uploads[$idx-1]);
        else $this->partner_uploads[$idx-1] = $tmpUp;
    }

    public function movePartnerDown(int $idx): void
    {
        if ($idx >= count($this->partners)-1) return;
        $tmp = $this->partners[$idx+1];
        $this->partners[$idx+1] = $this->partners[$idx];
        $this->partners[$idx] = $tmp;
        $tmpUp = $this->partner_uploads[$idx] ?? null;
        $next = $this->partner_uploads[$idx+1] ?? null;
        if ($next === null) unset($this->partner_uploads[$idx]);
        else $this->partner_uploads[$idx] = $next;
        if ($tmpUp === null) unset($this->partner_uploads[$idx+1]);
        else $this->partner_uploads[$idx+1] = $tmpUp;
    }

    public function removePartnerLogo(int $idx): void
    {
        $img = $this->partners[$idx]['logo'] ?? null;
        if ($img) {
            ImageService::delete($img);
            $this->partners[$idx]['logo'] = null;
            SiteSetting::set('partners', $this->partners, 'partners');
            $this->successMsg = 'Logo partenaire supprimé.';
        }
    }

    public function updatedMediaUpload(): void
    {
        // Auto-store dès sélection (UX médiatèque instantanée) — valide puis délègue à storeMedia()
        if ($this->media_upload) {
            $this->storeMedia();
        }
    }

    public function removeMedia(string $id): void
    {
        $this->authorize('cms.manage');
        $media = collect($this->media_library)->firstWhere('id', $id);
        if ($media) {
            ImageService::delete($media['path']);
            $this->media_library = array_values(array_filter($this->media_library, fn($m) => $m['id'] != $id));
            SiteSetting::set('media_library', $this->media_library, 'media');
            $this->successMsg = 'Média supprimé.';
            session()->flash('success', $this->successMsg);
        }
    }

    public function storeMedia(): void
    {
        $this->authorize('cms.manage');
        if (!$this->media_upload) return;

        $files = is_array($this->media_upload) ? $this->media_upload : [$this->media_upload];
        // Validation manuelle par fichier pour message clair (Livewire validate sur array d'upload est fragile en auto)
        $validFiles = [];
        foreach ($files as $file) {
            if (!$file) continue;
            try {
                validator(['file' => $file], ['file' => ['required','image','max:5120']])->validate();
                $validFiles[] = $file;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->addError('media_upload', $file->getClientOriginalName().': image invalide ou >5Mo.');
            }
        }
        if (empty($validFiles)) {
            $this->media_upload = null;
            return;
        }
        if (count($this->media_library) + count($validFiles) > 100) {
            $this->addError('media_upload', 'Bibliothèque limitée à 100 images — supprimez des médias avant d’en ajouter.');
            $this->media_upload = null;
            return;
        }
        foreach ($validFiles as $file) {
            $path = ImageService::storeOptimized($file, 'cms/media', 'public');
            if (!$path) continue;
            $this->media_library[] = [
                'id' => (string) Str::uuid(),
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'created_at' => now()->toDateTimeString(),
            ];
        }
        SiteSetting::set('media_library', $this->media_library, 'media');
        $this->media_upload = null;
        $this->successMsg = count($validFiles) . ' image(s) ajoutée(s) à la bibliothèque.';
        session()->flash('success', $this->successMsg);
    }

    public function applyMedia(string $id, string $target): void
    {
        $this->authorize('cms.manage');
        $media = collect($this->media_library)->firstWhere('id', $id);
        if (!$media || empty($media['path'])) {
            $this->addError('media_library', 'Média introuvable.');
            return;
        }
        $path = $media['path'];
        // Targets supportés: hero_slide1, hero_slide2, banner, about, services_hero, programs_hero, projects_hero, posts_hero, contact_hero, header_logo, seo_og, shared_hero, team:{idx}, service:{idx}
        if ($target === 'hero_slide1' || $target === 'hero_slide2') {
            $hero = SiteSetting::get('home.hero', []);
            $key = $target === 'hero_slide1' ? 'slide1_image' : 'slide2_image';
            if (!empty($hero[$key])) ImageService::delete($hero[$key]);
            $hero[$key] = $path;
            SiteSetting::set('home.hero', $hero, 'home');
            if ($target === 'hero_slide1') $this->hero_slide1_existing = $path; else $this->hero_slide2_existing = $path;
            $this->successMsg = "Média appliqué à Hero ".($target==='hero_slide1'?'Slide 1':'Slide 2')." — pensé à Enregistrer Accueil si besoin.";
        } elseif ($target === 'banner') {
            $banner = SiteSetting::get('home.banner', []);
            if (!empty($banner['image'])) ImageService::delete($banner['image']);
            $banner['image'] = $path;
            SiteSetting::set('home.banner', $banner, 'home');
            $this->banner_image_existing = $path;
            $this->successMsg = 'Média appliqué à Bannière Accueil.';
        } elseif ($target === 'about') {
            $hero = SiteSetting::get('about.hero', []);
            if (!empty($hero['image'])) ImageService::delete($hero['image']);
            $hero['image'] = $path;
            SiteSetting::set('about.hero', $hero, 'about');
            $this->about_image_existing = $path;
            $this->successMsg = 'Média appliqué à À propos.';
        } elseif (in_array($target, ['services_hero','programs_hero','projects_hero','posts_hero','contact_hero'], true)) {
            $group = explode('_', $target)[0]; // services, programs, etc.
            $hero = SiteSetting::get($group.'.hero', []);
            if (!empty($hero['image'])) ImageService::delete($hero['image']);
            $hero['image'] = $path;
            SiteSetting::set($group.'.hero', $hero, $group);
            $prop = $target.'_image_existing';
            if (property_exists($this, $prop)) $this->$prop = $path;
            $this->successMsg = "Média appliqué à Hero ".ucfirst($group).".";
        } elseif ($target === 'header_logo') {
            $header = SiteSetting::get('header', []);
            if (!empty($header['logo'])) ImageService::delete($header['logo']);
            $header['logo'] = $path;
            SiteSetting::set('header', $header, 'header');
            $this->header_logo_existing = $path;
            $this->successMsg = 'Média appliqué au Logo Header.';
        } elseif ($target === 'seo_og') {
            $seo = SiteSetting::get('seo', []);
            if (!empty($seo['og_image'])) ImageService::delete($seo['og_image']);
            $seo['og_image'] = $path;
            SiteSetting::set('seo', $seo, 'seo');
            $this->seo_og_image_existing = $path;
            $this->seo = array_merge($this->seo, ['og_image' => $path]);
            $this->successMsg = 'Média appliqué à SEO OG Image.';
        } elseif ($target === 'shared_hero') {
            $hero = SiteSetting::get('hero.shared', []);
            if (!empty($hero['image'])) ImageService::delete($hero['image']);
            $hero['image'] = $path;
            SiteSetting::set('hero.shared', $hero, 'hero');
            $this->shared_hero_image_existing = $path;
            $this->successMsg = 'Média appliqué au Hero partagé — tous les heroes utilisent cette image.';
        } elseif (str_starts_with($target, 'team:')) {
            $idx = (int) substr($target, 5);
            if (!isset($this->team_members[$idx])) { $this->addError('media_library', 'Membre équipe introuvable.'); return; }
            $old = $this->team_members[$idx]['avatar'] ?? null;
            if ($old) ImageService::delete($old);
            $this->team_members[$idx]['avatar'] = $path;
            SiteSetting::set('home.team', $this->team_members, 'home');
            $this->successMsg = "Média appliqué à Équipe #".($idx+1).".";
        } elseif (str_starts_with($target, 'service:')) {
            $idx = (int) substr($target, 8);
            if (!isset($this->services[$idx])) { $this->addError('media_library', 'Service introuvable.'); return; }
            $old = $this->services[$idx]['image'] ?? null;
            if ($old) ImageService::delete($old);
            $this->services[$idx]['image'] = $path;
            SiteSetting::set('services.list', $this->services, 'services');
            $this->successMsg = "Média appliqué au Service #".($idx+1)." (".($this->services[$idx]['key']??'').").";
        } else {
            $this->addError('media_library', 'Cible invalide.');
            return;
        }
        session()->flash('success', $this->successMsg);
    }

    public function applyPickedMedia(string $pickerKey): void
    {
        $id = $this->media_picker[$pickerKey] ?? null;
        if (!$id) {
            $this->addError('media_picker.'.$pickerKey, 'Choisissez un média.');
            return;
        }
        // Map picker keys to applyMedia targets
        $map = [
            'hero_slide1' => 'hero_slide1',
            'hero_slide2' => 'hero_slide2',
            'banner' => 'banner',
            'about' => 'about',
            'services_hero' => 'services_hero',
            'programs_hero' => 'programs_hero',
            'projects_hero' => 'projects_hero',
            'posts_hero' => 'posts_hero',
            'contact_hero' => 'contact_hero',
            'header_logo' => 'header_logo',
            'seo_og' => 'seo_og',
            'shared_hero' => 'shared_hero',
        ];
        if (isset($map[$pickerKey])) {
            $this->applyMedia($id, $map[$pickerKey]);
            return;
        }
        if (str_starts_with($pickerKey, 'team_')) {
            $idx = (int) substr($pickerKey, 5);
            $this->applyMedia($id, 'team:'.$idx);
            return;
        }
        if (str_starts_with($pickerKey, 'service_')) {
            $idx = (int) substr($pickerKey, 8);
            $this->applyMedia($id, 'service:'.$idx);
            return;
        }
        $this->addError('media_picker.'.$pickerKey, 'Cible invalide.');
    }

    public function getFilteredMediaLibraryProperty(): array
    {
        if (trim($this->media_search) === '') return $this->media_library;
        $q = mb_strtolower(trim($this->media_search));
        return array_values(array_filter($this->media_library, fn($m) => str_contains(mb_strtolower($m['name'] ?? ''), $q) || str_contains(mb_strtolower($m['path'] ?? ''), $q)));
    }

    public function addSocialNetwork(): void
    {
        $this->social_networks[] = ['name' => '', 'url' => '', 'icon' => ''];
    }

    public function removeSocialNetwork(int $idx): void
    {
        unset($this->social_networks[$idx]);
        $this->social_networks = array_values($this->social_networks);
    }

    public function addFooterLink(): void
    {
        $this->footer_links[] = ['label' => '', 'url' => ''];
    }

    public function removeFooterLink(int $idx): void
    {
        unset($this->footer_links[$idx]);
        $this->footer_links = array_values($this->footer_links);
    }

    // Pagination — unique
    public function gotoPage(int $page): void
    {
        // Generic pagination clamp — based on current active collection size
        $count = match($this->activeTab) {
            'testimonials' => count($this->testimonials),
            'partners' => count($this->partners),
            default => count($this->services),
        };
        $max = (int) ceil($count / $this->perPage);
        $this->page = max(1, min($page, max(1,$max)));
    }

    public function getPaginatedServicesProperty(): array
    {
        $offset = ($this->page - 1) * $this->perPage;
        return array_slice($this->services, $offset, $this->perPage, true);
    }

    public function getTotalPagesProperty(): int
    {
        return (int) max(1, ceil(count($this->services) / $this->perPage));
    }

    public function getPaginatedTestimonialsProperty(): array
    {
        $offset = ($this->page - 1) * $this->perPage;
        return array_slice($this->testimonials, $offset, $this->perPage, true);
    }

    public function getTotalTestimonialPagesProperty(): int
    {
        return (int) max(1, ceil(count($this->testimonials) / $this->perPage));
    }

    public function getPaginatedPartnersProperty(): array
    {
        $offset = ($this->page - 1) * $this->perPage;
        return array_slice($this->partners, $offset, $this->perPage, true);
    }

    public function getTotalPartnerPagesProperty(): int
    {
        return (int) max(1, ceil(count($this->partners) / $this->perPage));
    }
}; ?>


<section class="w-full p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-zinc-500">
                <a href="{{ route('admin.cms.index') }}" wire:navigate class="hover:text-primary">CMS</a>
                <span>›</span>
                <span class="font-medium text-zinc-900">Tout-en-un</span>
            </div>
            <flux:heading size="xl" class="mt-1">CMS — Tout-en-un</flux:heading>
            <flux:text>Éditez toute la vitrine sans code — hero, stats, À propos, Services, SEO, Thème. Images converties en WebP automatiquement, mise à jour instantanée.</flux:text>
        </div>
        <div class="flex gap-2">
            <flux:button :href="route('admin.cms.history')" wire:navigate variant="ghost" icon="clock">Historique</flux:button>
            <flux:button :href="route('home')" target="_blank" variant="ghost" icon="arrow-top-right-on-square">Voir vitrine</flux:button>
            <flux:button :href="route('admin.cms.index')" wire:navigate variant="ghost">Retour pages</flux:button>
        </div>
    </div>

    @if($successMsg ?? session('success'))
        <div class="mt-4 flex items-start gap-2 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
            <flux:icon.check-circle class="size-5 shrink-0" />
            <span>{{ $successMsg ?? session('success') }}</span>
        </div>
    @endif
    @php
        $phpUploadMax = ini_get('upload_max_filesize');
        $phpPostMax = ini_get('post_max_size');
        $phpUploadBytes = (int) filter_var($phpUploadMax, FILTER_SANITIZE_NUMBER_INT) * (str_contains($phpUploadMax, 'M') ? 1024*1024 : (str_contains($phpUploadMax, 'K') ? 1024 : 1));
        $isUploadLimited = $phpUploadBytes < 5*1024*1024;
        $gdMissing = !extension_loaded('gd') && !extension_loaded('imagick');
    @endphp
    @if($isUploadLimited || $gdMissing)
        <div class="mt-4 rounded-lg border {{ $gdMissing ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-amber-200 bg-amber-50 text-amber-800' }} p-3 text-sm">
            @if($isUploadLimited)
                <div class="flex gap-2"><flux:icon.exclamation-triangle class="size-5 shrink-0" /> <span><strong>Limite PHP actuelle :</strong> <code>upload_max_filesize={{ $phpUploadMax }}</code>, <code>post_max_size={{ $phpPostMax }}</code> — toute image > {{ $phpUploadMax }} échouera avec “failed to upload”. Pour autoriser 5 Mo, lancez <code>php -d upload_max_filesize=8M -d post_max_size=10M artisan serve</code> ou augmentez <code>php.ini</code> / <code>.htaccess</code> (<code>php_value upload_max_filesize 8M</code>). <code>.user.ini</code> 8M a été ajouté dans <code>public/</code>.</span></div>
            @endif
            @if($gdMissing)
                <div class="mt-2 flex gap-2"><flux:icon.information-circle class="size-5 shrink-0" /> <span><strong>GD/Imagick manquant :</strong> conversion WebP/AVIF désactivée — les images sont stockées telles quelles. Installez <code>php8.4-gd</code> (<code>sudo apt install php8.4-gd && sudo systemctl restart apache2</code> ou <code>sudo service php8.4-fpm restart</code>) pour optimiser.</span></div>
            @endif
        </div>
    @endif

    <div class="mt-6 flex flex-wrap gap-2 border-b border-zinc-200" role="tablist">
        @foreach(['home' => 'Accueil', 'about' => 'À propos', 'services' => 'Services', 'programs' => 'Lotissements', 'projects' => 'Réalisations', 'posts' => 'Actualités', 'contact' => 'Contact', 'hero' => 'Hero Global', 'global' => 'Global/Footer', 'header' => 'Header/Nav', 'seo' => 'SEO', 'media' => 'Médias', 'theme' => 'Thème'] as $key => $label)
            <button wire:click="$set('activeTab', '{{ $key }}')" role="tab" aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}" class="px-4 py-2 text-sm font-bold tracking-widest transition {{ $activeTab === $key ? 'border-b-2 border-primary text-primary' : 'text-zinc-500 hover:text-zinc-800' }}">{{ $label }}</button>
        @endforeach
    </div>

    <div class="mt-4 flex gap-2">
        <flux:button size="xs" variant="ghost" icon="chevron-down" onclick="document.querySelectorAll('section .rounded-2xl').forEach(c=>{const h=c.querySelector('[data-flux-heading]'); if(h) c.querySelectorAll(':scope > *:not([data-flux-heading])').forEach(n=>n.style.display='');})">Tout déplier</flux:button>
        <flux:button size="xs" variant="ghost" icon="chevron-up" onclick="document.querySelectorAll('section .rounded-2xl').forEach(c=>{const h=c.querySelector('[data-flux-heading]'); if(h) c.querySelectorAll(':scope > *:not([data-flux-heading])').forEach(n=>n.style.display='none');})">Tout replier</flux:button>
        <span class="text-xs text-zinc-400 self-center">Cards repliables — clique sur le titre pour replier</span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const init = () => {
                document.querySelectorAll('section .rounded-2xl.border').forEach(card => {
                    const heading = card.querySelector('[data-flux-heading]');
                    if (!heading || heading.dataset.collapsible) return;
                    heading.dataset.collapsible = '1';
                    heading.style.cursor = 'pointer';
                    heading.title = 'Cliquer pour replier/déplier';
                    heading.addEventListener('click', () => {
                        const toToggle = Array.from(card.children).filter(el => el !== heading && !el.matches('summary'));
                        const hidden = toToggle[0]?.style.display === 'none';
                        toToggle.forEach(el => el.style.display = hidden ? '' : 'none');
                        heading.style.opacity = hidden ? '1' : '0.7';
                    });
                });
            };
            init();
            // Re-init after Livewire updates
            document.addEventListener('livewire:navigated', init);
            if (window.Livewire) window.Livewire.hook('morph.updated', init);
        });
    </script>

    @if($activeTab === 'home')
        <div class="pt-6">
            <form wire:submit="saveHome" class="space-y-6 max-w-3xl">
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Hero Slider</flux:heading>
                    <flux:input wire:model="hero_badge" label="Badge (bandeau)" placeholder="SARL depuis 2022 — IDU CI-2022-0016466 Q" />
                    <div class="grid md:grid-cols-3 gap-3 mt-3">
                        <flux:input wire:model="hero_title1" label="Titre ligne 1 *" placeholder="SIBEA-CI" />
                        <flux:input wire:model="hero_title2" label="Ligne 2 *" placeholder="Bâtir l'avenir" />
                        <flux:input wire:model="hero_title3" label="Ligne 3" placeholder="en Côte d'Ivoire" />
                    </div>
                    <flux:textarea wire:model="hero_subtitle" label="Sous-titre" rows="2" placeholder="Multisectorielle BTP, Électricité..." />
                    <div class="grid md:grid-cols-2 gap-3 mt-3">
                        <flux:input wire:model="cta_primary" label="CTA principal" placeholder="NOS RÉALISATIONS" />
                        <flux:input wire:model="cta_secondary" label="CTA secondaire" placeholder="DEMANDER UN DEVIS" />
                    </div>
                    <div class="grid md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <flux:input type="file" wire:model="hero_slide1" label="Slide 1 — image (5 Mo, WebP auto)" accept="image/*" />
                            @if($hero_slide1_existing)
                                <div class="mt-2 flex items-center gap-2 rounded-lg border p-2">
                                    <img src="{{ Storage::disk('public')->url($hero_slide1_existing) }}" alt="Slide 1" class="size-16 rounded object-cover" />
                                    <div class="flex-1 text-xs text-zinc-500 truncate">{{ $hero_slide1_existing }}</div>
                                    <flux:button size="xs" variant="ghost" wire:click="removeHeroImage('slide1')" wire:confirm="Supprimer cette image ?">Supprimer</flux:button>
                                </div>
                            @endif
                            @error('hero_slide1') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                            @if(count($media_library))
                                <div class="mt-2 flex gap-2 items-center">
                                    <select wire:model="media_picker.hero_slide1" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                        <option value="">— ou depuis médiathèque —</option>
                                        @foreach($media_library as $m)<option value="{{ $m['id'] }}">{{ Str::limit($m['name'],30) }}</option>@endforeach
                                    </select>
                                    <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('hero_slide1')">Utiliser</flux:button>
                                </div>
                                @error('media_picker.hero_slide1') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                            @else
                                <div class="mt-2 text-[11px] text-zinc-500">Aucun média — <button wire:click="$set('activeTab','media')" class="underline">ajouter dans Médias</button></div>
                            @endif
                        </div>
                        <div>
                            <flux:input type="file" wire:model="hero_slide2" label="Slide 2 — image (5 Mo, WebP auto)" accept="image/*" />
                            @if($hero_slide2_existing)
                                <div class="mt-2 flex items-center gap-2 rounded-lg border p-2">
                                    <img src="{{ Storage::disk('public')->url($hero_slide2_existing) }}" alt="Slide 2" class="size-16 rounded object-cover" />
                                    <div class="flex-1 text-xs text-zinc-500 truncate">{{ $hero_slide2_existing }}</div>
                                    <flux:button size="xs" variant="ghost" wire:click="removeHeroImage('slide2')" wire:confirm="Supprimer cette image ?">Supprimer</flux:button>
                                </div>
                            @endif
                            @error('hero_slide2') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                            @if(count($media_library))
                                <div class="mt-2 flex gap-2 items-center">
                                    <select wire:model="media_picker.hero_slide2" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                        <option value="">— ou depuis médiathèque —</option>
                                        @foreach($media_library as $m)<option value="{{ $m['id'] }}">{{ Str::limit($m['name'],30) }}</option>@endforeach
                                    </select>
                                    <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('hero_slide2')">Utiliser</flux:button>
                                </div>
                                @error('media_picker.hero_slide2') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                            @endif
                        </div>
                    </div>
                    <div wire:loading wire:target="hero_slide1,hero_slide2" class="text-xs text-zinc-500">Upload en cours…</div>
                </div>

                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Stats — 5 compteurs</flux:heading>
                    <div class="grid md:grid-cols-3 gap-3">
                        <flux:input wire:model="stat_projects" type="number" label="Projets livrés" />
                        <flux:input wire:model="stat_clients" type="number" label="Clients satisfaits" />
                        <flux:input wire:model="stat_workers" type="number" label="Ouvriers" />
                    </div>
                    <div class="grid md:grid-cols-2 gap-3 mt-3">
                        <flux:input wire:model="stat_awards" type="number" label="Prix remportés" />
                        <flux:input wire:model="stat_surface" type="number" label="Surface aménagée (m²)" />
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Pourquoi nous choisir — Titre + 4 items (optionnel)</flux:heading>
                    <flux:input wire:model="why_title" label="Titre section" placeholder="POURQUOI NOUS CHOISIR ?" class="mb-3" />
                    @foreach($why_items as $i => $item)
                        <div class="mb-3 rounded-lg bg-zinc-50 p-3">
                            <div class="grid md:grid-cols-2 gap-2">
                                <flux:input wire:model="why_items.{{ $i }}.label" label="Label" />
                                <flux:input wire:model="why_items.{{ $i }}.desc" label="Description" />
                            </div>
                        </div>
                    @endforeach
                    <div class="text-xs text-zinc-500">Ces items s’affichent dans la section “Pourquoi nous choisir” de la page d’accueil.</div>
                </div>

                <flux:button type="submit" variant="primary" icon="check">Enregistrer Accueil</flux:button>
            </form>

            <!-- 4 Pôles transversaux — gérés depuis Accueil (source services.list) -->
            <div class="mt-10 border-t border-zinc-200 pt-6">
                <flux:heading size="sm" class="mb-3">4 Pôles transversaux — Accueil</flux:heading>
                <flux:text class="mb-3 text-xs text-zinc-500">Ces 4 premiers services sont affichés sur l'Accueil. Gérez-les ici ou dans l'onglet Services (même source <code>services.list</code>).</flux:text>
                <form wire:submit="saveServices" class="space-y-4 max-w-4xl">
                    @foreach(array_slice($services,0,4) as $i => $svc)
                        <div class="rounded-xl border p-4">
                            <div class="flex justify-between items-center"><span class="text-sm font-bold">Pôle {{ $i+1 }} — <span class="font-mono text-xs">{{ $svc['key'] }}</span></span>
                                <div class="flex gap-1 items-center">
                                    <flux:button size="xs" variant="ghost" :disabled="$i===0" wire:click="moveServiceUp({{ $i }})">↑</flux:button>
                                    <flux:button size="xs" variant="ghost" :disabled="$i===3 || $i===count($services)-1" wire:click="moveServiceDown({{ $i }})">↓</flux:button>
                                </div>
                            </div>
                            <div class="grid md:grid-cols-2 gap-3 mt-3">
                                <flux:input wire:model="services.{{ $i }}.key" label="Clé (slug) *" placeholder="btp" />
                                <flux:input wire:model="services.{{ $i }}.title" label="Titre *" />
                            </div>
                            <flux:textarea wire:model="services.{{ $i }}.desc" label="Description" rows="2" placeholder="Description courte 160c" />
                            <div class="mt-2">
                                <flux:input type="file" wire:model="service_uploads.{{ $i }}" label="Image (5 Mo, WebP auto)" accept="image/*" />
                                @if($svc['image'] ?? null)
                                    <div class="mt-2 flex items-center gap-2 rounded-lg border p-2">
                                        <img src="{{ Storage::disk('public')->url($svc['image']) }}" alt="{{ $svc['title'] }}" class="size-12 rounded object-cover" />
                                        <div class="text-xs text-zinc-500 truncate flex-1">{{ $svc['image'] }}</div>
                                        <flux:button size="xs" variant="ghost" wire:click="removeServiceImage({{ $i }})" wire:confirm="Supprimer l'image ?">Suppr. image</flux:button>
                                    </div>
                                @endif
                                @if(count($media_library))
                                    <div class="mt-2 flex gap-2 items-center">
                                        <select wire:model="media_picker.service_{{ $i }}" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                            <option value="">— ou depuis médiathèque —</option>
                                            @foreach($media_library as $mm)<option value="{{ $mm['id'] }}">{{ Str::limit($mm['name'],25) }}</option>@endforeach
                                        </select>
                                        <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('service_{{ $i }}')">Utiliser</flux:button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    <div class="flex gap-2">
                        <flux:button type="submit" variant="primary">Enregistrer Pôles</flux:button>
                        <flux:button variant="ghost" wire:click="$set('activeTab','services')">Gérer tous les services →</flux:button>
                    </div>
                </form>
            </div>

            <div class="mt-6 rounded-lg bg-zinc-50 p-3 text-xs text-zinc-600">ℹ️ <strong>Témoignages</strong> et <strong>Partenaires</strong> affichés sur l'Accueil sont désormais gérés via la <strong>Sidebar → Témoignages / Partenaires</strong> (source Eloquent <code>is_published/position</code>), plus dans cet onglet. Voir <code>front/home.blade.php:303/371</code>.</div>

            <!-- Offres & Détails — NOS SERVICES -->
            <div class="mt-10 border-t border-zinc-200 pt-6">
                <flux:heading size="sm" class="mb-3">NOS SERVICES — Offres & Détails</flux:heading>
                <form wire:submit="saveHomeOffers" class="space-y-4 max-w-4xl">
                    <div class="rounded-xl border p-4">
                        <flux:heading size="xs" class="mb-2">Offres (5) — colonne gauche</flux:heading>
                        @foreach($home_offers as $i => $offer)
                            <div class="flex gap-2 mb-2">
                                <flux:input wire:model="home_offers.{{ $i }}" label="Offre {{ $i+1 }}" class="flex-1" />
                                <flux:button size="xs" variant="danger" wire:click="removeHomeOffer({{ $i }})">Suppr</flux:button>
                            </div>
                        @endforeach
                        <flux:button size="xs" variant="ghost" wire:click="addHomeOffer">Ajouter offre</flux:button>
                    </div>
                    <div class="rounded-xl border p-4">
                        <flux:heading size="xs" class="mb-2">Détails (4) — colonne centre</flux:heading>
                        @foreach($home_details as $i => $det)
                            <div class="mb-3 rounded-lg bg-zinc-50 p-3">
                                <flux:input wire:model="home_details.{{ $i }}.title" label="Titre" />
                                <flux:textarea wire:model="home_details.{{ $i }}.desc" label="Description" rows="2" />
                                <flux:button size="xs" variant="danger" wire:click="removeHomeDetail({{ $i }})" class="mt-2">Supprimer</flux:button>
                            </div>
                        @endforeach
                        <flux:button size="xs" variant="ghost" wire:click="addHomeDetail">Ajouter détail</flux:button>
                    </div>
                    <flux:button type="submit" variant="primary">Enregistrer Offres & Détails</flux:button>
                </form>
            </div>

            <!-- Bannière 1981 -->
            <div class="mt-10 border-t border-zinc-200 pt-6">
                <flux:heading size="sm" class="mb-3">Bannière — Entrepreneurs depuis 1981</flux:heading>
                <form wire:submit="saveBanner" class="space-y-4 max-w-3xl">
                    <flux:input wire:model="banner_title" label="Titre bannière" />
                    <div class="grid md:grid-cols-2 gap-3">
                        <flux:input wire:model="banner_cta_label" label="Texte CTA" />
                        <flux:input wire:model="banner_cta_url" label="URL CTA" />
                    </div>
                    <flux:input type="file" wire:model="banner_image" label="Image fond (optionnel)" accept="image/*" />
                    @if($banner_image_existing)
                        <div class="flex items-center gap-2 rounded-lg border p-2">
                            <img src="{{ Storage::disk('public')->url($banner_image_existing) }}" class="size-12 rounded object-cover" />
                            <flux:button size="xs" variant="ghost" wire:click="removeBannerImage" wire:confirm="Supprimer ?">Supprimer image</flux:button>
                        </div>
                    @endif
                    @if(count($media_library))
                        <div class="flex gap-2 items-center">
                            <select wire:model="media_picker.banner" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                <option value="">— ou depuis médiathèque —</option>
                                @foreach($media_library as $m)<option value="{{ $m['id'] }}">{{ Str::limit($m['name'],30) }}</option>@endforeach
                            </select>
                            <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('banner')">Utiliser</flux:button>
                        </div>
                        @error('media_picker.banner') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                    @endif
                    <flux:button type="submit" variant="primary">Enregistrer Bannière</flux:button>
                </form>
            </div>

            <!-- Équipe -->
            <div class="mt-10 border-t border-zinc-200 pt-6">
                <flux:heading size="sm" class="mb-3">Notre Équipe — Accueil</flux:heading>
                <form wire:submit="saveTeam" class="space-y-4 max-w-4xl">
                    @foreach($team_members as $i => $m)
                        <div class="rounded-xl border p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold">Membre {{ $i+1 }}</span>
                                <div class="flex gap-1">
                                    <flux:button size="xs" variant="ghost" :disabled="$i===0" wire:click="moveTeamUp({{ $i }})">↑</flux:button>
                                    <flux:button size="xs" variant="ghost" :disabled="$i===count($team_members)-1" wire:click="moveTeamDown({{ $i }})">↓</flux:button>
                                    <flux:button size="xs" variant="danger" wire:click="removeTeamMember({{ $i }})">Supprimer</flux:button>
                                </div>
                            </div>
                            <div class="grid md:grid-cols-2 gap-3 mt-3">
                                <flux:input wire:model="team_members.{{ $i }}.name" label="Nom *" />
                                <flux:input wire:model="team_members.{{ $i }}.role" label="Rôle" />
                            </div>
                            <flux:input type="file" wire:model="team_uploads.{{ $i }}" label="Avatar" accept="image/*" class="mt-2" />
                            @if(!empty($m['avatar']))
                                <div class="flex items-center gap-2 mt-2">
                                    <img src="{{ Storage::disk('public')->url($m['avatar']) }}" class="size-12 rounded-full object-cover" />
                                    <flux:button size="xs" variant="ghost" wire:click="removeTeamAvatar({{ $i }})">Suppr. avatar</flux:button>
                                </div>
                            @endif
                            @if(count($media_library))
                                <div class="mt-2 flex gap-2 items-center">
                                    <select wire:model="media_picker.team_{{ $i }}" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                        <option value="">— ou depuis médiathèque —</option>
                                        @foreach($media_library as $mm)<option value="{{ $mm['id'] }}">{{ Str::limit($mm['name'],25) }}</option>@endforeach
                                    </select>
                                    <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('team_{{ $i }}')">Utiliser</flux:button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                    <flux:button variant="ghost" wire:click="addTeamMember">Ajouter membre</flux:button>
                    <flux:button type="submit" variant="primary">Enregistrer Équipe</flux:button>
                </form>
            </div>
        </div>
    @elseif($activeTab === 'about')
        <div class="pt-6">
            <form wire:submit="saveAbout" class="space-y-4 max-w-3xl">
                @if($shared_hero_image_existing)
                    <div class="rounded-lg bg-cyan-50 p-3 text-xs text-cyan-800">Image hero partagée active : <code class="bg-white px-1 rounded">{{ $shared_hero_image_existing }}</code> — le hero À propos utilise cette image partagée. <button wire:click="$set('activeTab','hero')" class="underline">Gérer dans Hero Global →</button></div>
                @endif
                <flux:input wire:model="about_title" label="Titre *" />
                <flux:input wire:model="about_badge" label="Badge" placeholder="LABORATOIRE URBAIN • ABIDJAN 2020" />
                <flux:textarea wire:model="about_subtitle" label="Sous-titre / Body" rows="3" placeholder="Description laboratoire..." />
                <div>
                    <flux:input type="file" wire:model="about_image" label="Image groupe (5 Mo, WebP auto)" accept="image/*" />
                    @if($about_image_existing)
                        <div class="mt-2 flex items-center gap-2 rounded-lg border p-2">
                            <img src="{{ Storage::disk('public')->url($about_image_existing) }}" alt="À propos" class="size-16 rounded object-cover" />
                            <div class="flex-1 text-xs text-zinc-500 truncate">{{ $about_image_existing }}</div>
                            <flux:button size="xs" variant="ghost" wire:click="removeAboutImage" wire:confirm="Supprimer cette image ?">Supprimer</flux:button>
                        </div>
                    @endif
                    @if(count($media_library))
                        <div class="mt-2 flex gap-2 items-center">
                            <select wire:model="media_picker.about" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                <option value="">— ou depuis médiathèque —</option>
                                @foreach($media_library as $m)<option value="{{ $m['id'] }}">{{ Str::limit($m['name'],30) }}</option>@endforeach
                            </select>
                            <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('about')">Utiliser</flux:button>
                        </div>
                    @endif
                </div>
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Why Choose Us — 4 progress bars (0-100%)</flux:heading>
                    @foreach($about_progress as $i => $p)
                        <div class="grid md:grid-cols-[1fr_100px] gap-2 mb-3 items-end">
                            <flux:input wire:model="about_progress.{{ $i }}.label" label="Label" />
                            <flux:input wire:model="about_progress.{{ $i }}.pct" type="number" label="%" min="0" max="100" />
                        </div>
                    @endforeach
                </div>
                <flux:button type="submit" variant="primary">Enregistrer À propos</flux:button>
            </form>
        </div>
    @elseif($activeTab === 'services')
        <div class="pt-6">
            <form wire:submit="saveServicesHero" class="space-y-4 max-w-4xl mb-8">
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Hero — Services</flux:heading>
                    @if($shared_hero_image_existing)
                        <div class="rounded-lg bg-cyan-50 p-3 text-xs text-cyan-800 mb-3">Image partagée active : <code class="bg-white px-1 rounded">{{ $shared_hero_image_existing }}</code> — tous les heroes utilisent cette image. <button wire:click="$set('activeTab','hero')" class="underline">Gérer dans Hero Global →</button></div>
                    @endif
                    <flux:input wire:model="services_hero_title" label="Titre hero" placeholder="Nos Services" />
                    <flux:input wire:model="services_hero_badge" label="Badge" placeholder="SERVICES — 6 EXPERTISES" />
                    <flux:textarea wire:model="services_hero_body" label="Sous-titre hero" rows="2" placeholder="6 expertises ivoiriennes..." />
                    <div class="mt-3">
                        <flux:input type="file" wire:model="services_hero_image" label="Image hero (5 Mo, WebP auto)" accept="image/*" />
                        <div class="text-xs text-zinc-500 mt-1">Limite serveur actuelle: {{ $phpUploadMax ?? ini_get('upload_max_filesize') }} (validation 5 Mo). Si “failed to upload”, compressez l’image &lt; {{ $phpUploadMax }} ou augmentez <code>php.ini</code>/<code>.htaccess</code>.</div>
                        @if($services_hero_image_existing)
                            <div class="mt-2 flex items-center gap-2 rounded-lg border p-2">
                                <img src="{{ Storage::disk('public')->url($services_hero_image_existing) }}" alt="Hero Services" class="size-16 rounded object-cover" />
                                <div class="flex-1 text-xs text-zinc-500 truncate">{{ $services_hero_image_existing }}</div>
                                <flux:button size="xs" variant="ghost" wire:click="removeServicesHeroImage" wire:confirm="Supprimer cette image ?">Supprimer</flux:button>
                            </div>
                        @endif
                        @if(count($media_library))
                            <div class="mt-2 flex gap-2 items-center">
                                <select wire:model="media_picker.services_hero" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                    <option value="">— ou depuis médiathèque —</option>
                                    @foreach($media_library as $m)<option value="{{ $m['id'] }}">{{ Str::limit($m['name'],30) }}</option>@endforeach
                                </select>
                                <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('services_hero')">Utiliser</flux:button>
                            </div>
                        @endif
                        @error('services_hero_image') <div class="text-xs text-red-600 font-medium">{{ $message }} — vérifiez que l’image est &lt; {{ $phpUploadMax ?? '2M' }} et est un vrai JPEG/PNG/WebP.</div> @enderror
                    </div>
                    <div wire:loading wire:target="services_hero_image" class="text-xs text-zinc-500">Upload en cours…</div>
                </div>
                <flux:button type="submit" variant="primary">Enregistrer Hero Services</flux:button>
            </form>
            <form wire:submit="saveServices" class="space-y-4 max-w-4xl">
                @foreach($services as $i => $svc)
                    <div class="rounded-xl border p-4">
                        <div class="flex justify-between items-center"><span class="text-sm font-bold">Service {{ $i+1 }} — <span class="font-mono text-xs">{{ $svc['key'] }}</span></span>
                            <div class="flex gap-1 items-center">
                                <flux:button size="xs" variant="ghost" :disabled="$i===0" wire:click="moveServiceUp({{ $i }})">↑</flux:button>
                                <flux:button size="xs" variant="ghost" :disabled="$i===count($services)-1" wire:click="moveServiceDown({{ $i }})">↓</flux:button>
                                @if(!empty($svc['image']))
                                    <flux:button size="xs" variant="ghost" wire:click="removeServiceImage({{ $i }})" wire:confirm="Supprimer l'image ?">Suppr. image</flux:button>
                                @endif
                                <flux:button size="xs" variant="danger" wire:click="removeService({{ $i }})" wire:confirm="Supprimer ce service ?">Supprimer</flux:button>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-3 mt-3">
                            <flux:input wire:model="services.{{ $i }}.key" label="Clé (slug) *" placeholder="btp" />
                            <flux:input wire:model="services.{{ $i }}.title" label="Titre *" />
                        </div>
                        <flux:textarea wire:model="services.{{ $i }}.desc" label="Description" rows="2" placeholder="Description courte 160c" />
                        <div class="mt-2">
                            <flux:input type="file" wire:model="service_uploads.{{ $i }}" label="Image (5 Mo, WebP auto)" accept="image/*" />
                            @if($svc['image'] ?? null)
                                <div class="mt-2 flex items-center gap-2 rounded-lg border p-2">
                                    <img src="{{ Storage::disk('public')->url($svc['image']) }}" alt="{{ $svc['title'] }}" class="size-12 rounded object-cover" />
                                    <div class="text-xs text-zinc-500 truncate flex-1">{{ $svc['image'] }}</div>
                                </div>
                            @endif
                            @if(count($media_library))
                                <div class="mt-2 flex gap-2 items-center">
                                    <select wire:model="media_picker.service_{{ $i }}" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                        <option value="">— ou depuis médiathèque —</option>
                                        @foreach($media_library as $mm)<option value="{{ $mm['id'] }}">{{ Str::limit($mm['name'],25) }}</option>@endforeach
                                    </select>
                                    <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('service_{{ $i }}')">Utiliser</flux:button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                <div class="flex gap-2">
                    <flux:button wire:click="addService" variant="ghost" icon="plus">Ajouter un service</flux:button>
                    <flux:button type="submit" variant="primary">Enregistrer Services</flux:button>
                </div>
                @error('services') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
            </form>
        </div>
    @elseif($activeTab === 'seo')
        <div class="pt-6">
            <form wire:submit="saveSeo" class="space-y-4 max-w-2xl">
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm">Accueil</flux:heading>
                    <flux:input wire:model="seo.home_title" label="Home title * (≤70c)" maxlength="70" />
                    <div class="text-xs text-zinc-500 text-right">{{ mb_strlen($seo['home_title'] ?? '') }}/70</div>
                    <flux:textarea wire:model="seo.home_desc" label="Home description (≤160c)" rows="2" maxlength="160" />
                    <div class="text-xs text-zinc-500 text-right">{{ mb_strlen($seo['home_desc'] ?? '') }}/160</div>
                </div>
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm">À propos</flux:heading>
                    <flux:input wire:model="seo.about_title" label="À propos title (≤70c)" />
                    <flux:textarea wire:model="seo.about_desc" label="À propos description (≤160c)" rows="2" />
                </div>
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm">Services</flux:heading>
                    <flux:input wire:model="seo.services_title" label="Services title (≤70c)" />
                    <flux:textarea wire:model="seo.services_desc" label="Services description (≤160c)" rows="2" />
                </div>
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Open Graph / Réseaux sociaux</flux:heading>
                    <flux:input wire:model="seo.og_title" label="OG Title (≤95c)" placeholder="Partage Facebook/LinkedIn" />
                    <flux:textarea wire:model="seo.og_description" label="OG Description (≤200c)" rows="2" />
                    <div class="grid md:grid-cols-3 gap-3 mt-3">
                        <flux:input wire:model="seo.og_type" label="OG Type" placeholder="website" />
                        <flux:input wire:model="seo.twitter_card" label="Twitter Card" placeholder="summary_large_image" />
                        <flux:input wire:model="seo.twitter_site" label="Twitter Site" placeholder="@sibea_ci" />
                    </div>
                    <flux:input wire:model="seo.robots" label="Robots" placeholder="index,follow" class="mt-3" />
                    <div class="mt-4">
                        <flux:input type="file" wire:model="seo_og_image" label="OG Image (5 Mo, WebP auto, 1200×630 recommandé)" accept="image/*" />
                        @if($seo_og_image_existing)
                            <div class="mt-2 flex items-center gap-2 rounded-lg border p-2">
                                <img src="{{ Storage::disk('public')->url($seo_og_image_existing) }}" alt="OG" class="size-16 rounded object-cover" />
                                <div class="flex-1 text-xs text-zinc-500 truncate">{{ $seo_og_image_existing }}</div>
                            </div>
                        @endif
                        @if(count($media_library))
                            <div class="mt-2 flex gap-2 items-center">
                                <select wire:model="media_picker.seo_og" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                    <option value="">— ou depuis médiathèque —</option>
                                    @foreach($media_library as $m)<option value="{{ $m['id'] }}">{{ Str::limit($m['name'],30) }}</option>@endforeach
                                </select>
                                <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('seo_og')">Utiliser</flux:button>
                            </div>
                        @endif
                        @error('seo_og_image') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>
                </div>
                <flux:button type="submit" variant="primary">Enregistrer SEO</flux:button>
                <div class="text-xs text-zinc-500">Conseil : inclure “Abidjan, Bingerville, Côte d’Ivoire” pour le référencement local.</div>
            </form>
        </div>
    @elseif($activeTab === 'theme')
        <div class="pt-6">
            <form wire:submit="saveTheme" class="space-y-4 max-w-xl">
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Couleurs du thème</flux:heading>
                    <div class="flex gap-4">
                        <flux:input wire:model.live="theme_primary" type="color" label="Primaire *" />
                        <flux:input wire:model.live="theme_accent" type="color" label="Accent *" />
                    </div>
                    <div class="mt-4 flex gap-3">
                        <div class="size-12 rounded-full border shadow" style="background: {{ $theme_primary }}"></div>
                        <div class="size-12 rounded-full border shadow" style="background: {{ $theme_accent }}"></div>
                        <div class="flex-1 rounded-lg p-3 text-sm text-white" style="background: {{ $theme_primary }}">Aperçu bouton primaire</div>
                    </div>
                    <div class="mt-3 rounded-lg p-3 text-sm border" style="background: {{ $theme_accent }}20; border-color: {{ $theme_accent }}; color: {{ $theme_accent }}">Accent — bordures, liens, hover</div>
                </div>
                <flux:button type="submit" variant="primary">Enregistrer Thème</flux:button>
                <div class="text-xs text-zinc-500">Utilisé via <code>SiteSetting::get('theme')</code> et CSS variables <code>--color-primary</code> / <code>--color-accent</code> dans <code>layouts/front</code>.</div>
            </form>
        </div>
    @elseif($activeTab === 'programs')
        <div class="pt-6">
            <form wire:submit="savePrograms" class="space-y-4 max-w-3xl">
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Hero — Lotissements / Viabilisation</flux:heading>
                    @if($shared_hero_image_existing)
                        <div class="rounded-lg bg-cyan-50 p-3 text-xs text-cyan-800 mb-3">Image partagée active : <code class="bg-white px-1 rounded">{{ $shared_hero_image_existing }}</code> — tous les heroes utilisent cette image. <button wire:click="$set('activeTab','hero')" class="underline">Gérer dans Hero Global →</button></div>
                    @endif
                    <flux:input wire:model="programs_hero_title" label="Titre *" placeholder="FONCIER — VIABILISATION" />
                    <flux:input wire:model="programs_hero_badge" label="Badge" placeholder="FONCIER — VIABILISATION" />
                    <flux:textarea wire:model="programs_hero_body" label="Sous-titre" rows="3" placeholder="Catalogue en temps réel..." />
                    <div class="mt-4">
                        <flux:input type="file" wire:model="programs_hero_image" label="Image de fond (5 Mo, WebP auto)" accept="image/*" />
                        @if($programs_hero_image_existing)
                            <div class="mt-2 flex items-center gap-2 rounded-lg border p-2">
                                <img src="{{ Storage::disk('public')->url($programs_hero_image_existing) }}" alt="Hero Programmes" class="size-16 rounded object-cover" />
                                <div class="flex-1 text-xs text-zinc-500 truncate">{{ $programs_hero_image_existing }}</div>
                                <flux:button size="xs" variant="ghost" wire:click="removeProgramsImage" wire:confirm="Supprimer cette image ?">Supprimer</flux:button>
                            </div>
                        @endif
                        @if(count($media_library))
                            <div class="mt-2 flex gap-2 items-center">
                                <select wire:model="media_picker.programs_hero" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                    <option value="">— ou depuis médiathèque —</option>
                                    @foreach($media_library as $m)<option value="{{ $m['id'] }}">{{ Str::limit($m['name'],30) }}</option>@endforeach
                                </select>
                                <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('programs_hero')">Utiliser</flux:button>
                            </div>
                        @endif
                        @error('programs_hero_image') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>
                    <div wire:loading wire:target="programs_hero_image" class="text-xs text-zinc-500">Upload en cours…</div>
                </div>
                <flux:button type="submit" variant="primary">Enregistrer Programmes</flux:button>
            </form>
        </div>
    @elseif($activeTab === 'projects')
        <div class="pt-6">
            <form wire:submit="saveProjects" class="space-y-4 max-w-3xl">
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Hero — Réalisations / Portfolio</flux:heading>
                    @if($shared_hero_image_existing)
                        <div class="rounded-lg bg-cyan-50 p-3 text-xs text-cyan-800 mb-3">Image partagée active : <code class="bg-white px-1 rounded">{{ $shared_hero_image_existing }}</code> — tous les heroes utilisent cette image. <button wire:click="$set('activeTab','hero')" class="underline">Gérer dans Hero Global →</button></div>
                    @endif
                    <flux:input wire:model="projects_hero_title" label="Titre *" placeholder="Réalisations contextualisées" />
                    <flux:input wire:model="projects_hero_badge" label="Badge" placeholder="PORTFOLIO — 4 PÔLES" />
                    <flux:textarea wire:model="projects_hero_body" label="Sous-titre" rows="3" placeholder="Chaque projet est une réponse concrète..." />
                    <div class="mt-4">
                        <flux:input type="file" wire:model="projects_hero_image" label="Image de fond (5 Mo, WebP auto)" accept="image/*" />
                        @if($projects_hero_image_existing)
                            <div class="mt-2 flex items-center gap-2 rounded-lg border p-2">
                                <img src="{{ Storage::disk('public')->url($projects_hero_image_existing) }}" alt="Hero Projets" class="size-16 rounded object-cover" />
                                <div class="flex-1 text-xs text-zinc-500 truncate">{{ $projects_hero_image_existing }}</div>
                                <flux:button size="xs" variant="ghost" wire:click="removeProjectsImage" wire:confirm="Supprimer cette image ?">Supprimer</flux:button>
                            </div>
                        @endif
                        @if(count($media_library))
                            <div class="mt-2 flex gap-2 items-center">
                                <select wire:model="media_picker.projects_hero" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                    <option value="">— ou depuis médiathèque —</option>
                                    @foreach($media_library as $m)<option value="{{ $m['id'] }}">{{ Str::limit($m['name'],30) }}</option>@endforeach
                                </select>
                                <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('projects_hero')">Utiliser</flux:button>
                            </div>
                        @endif
                        @error('projects_hero_image') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>
                    <div wire:loading wire:target="projects_hero_image" class="text-xs text-zinc-500">Upload en cours…</div>
                </div>
                <flux:button type="submit" variant="primary">Enregistrer Projets</flux:button>
            </form>
        </div>
    @elseif($activeTab === 'posts')
        <div class="pt-6">
            <form wire:submit="savePosts" class="space-y-4 max-w-3xl">
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Hero — Actualités / Recherche</flux:heading>
                    @if($shared_hero_image_existing)
                        <div class="rounded-lg bg-cyan-50 p-3 text-xs text-cyan-800 mb-3">Image partagée active : <code class="bg-white px-1 rounded">{{ $shared_hero_image_existing }}</code> — tous les heroes utilisent cette image. <button wire:click="$set('activeTab','hero')" class="underline">Gérer dans Hero Global →</button></div>
                    @endif
                    <flux:input wire:model="posts_hero_title" label="Titre *" placeholder="Actualités & Recherche" />
                    <flux:input wire:model="posts_hero_badge" label="Badge" placeholder="RECHERCHE — PUBLICATIONS" />
                    <flux:textarea wire:model="posts_hero_body" label="Sous-titre" rows="3" placeholder="Conseils foncier, normes BTP..." />
                    <div class="mt-4">
                        <flux:input type="file" wire:model="posts_hero_image" label="Image de fond (5 Mo, WebP auto)" accept="image/*" />
                        @if($posts_hero_image_existing)
                            <div class="mt-2 flex items-center gap-2 rounded-lg border p-2">
                                <img src="{{ Storage::disk('public')->url($posts_hero_image_existing) }}" alt="Hero Actualités" class="size-16 rounded object-cover" />
                                <div class="flex-1 text-xs text-zinc-500 truncate">{{ $posts_hero_image_existing }}</div>
                                <flux:button size="xs" variant="ghost" wire:click="removePostsImage" wire:confirm="Supprimer cette image ?">Supprimer</flux:button>
                            </div>
                        @endif
                        @if(count($media_library))
                            <div class="mt-2 flex gap-2 items-center">
                                <select wire:model="media_picker.posts_hero" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                    <option value="">— ou depuis médiathèque —</option>
                                    @foreach($media_library as $m)<option value="{{ $m['id'] }}">{{ Str::limit($m['name'],30) }}</option>@endforeach
                                </select>
                                <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('posts_hero')">Utiliser</flux:button>
                            </div>
                        @endif
                        @error('posts_hero_image') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>
                    <div wire:loading wire:target="posts_hero_image" class="text-xs text-zinc-500">Upload en cours…</div>
                </div>
                <flux:button type="submit" variant="primary">Enregistrer Actualités</flux:button>
            </form>
        </div>
    @elseif($activeTab === 'contact')
        <div class="pt-6">
            <form wire:submit="saveContact" class="space-y-4 max-w-3xl">
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Hero — Contact & Devis</flux:heading>
                    @if($shared_hero_image_existing)
                        <div class="rounded-lg bg-cyan-50 p-3 text-xs text-cyan-800 mb-3">Image partagée active : <code class="bg-white px-1 rounded">{{ $shared_hero_image_existing }}</code> — tous les heroes utilisent cette image. <button wire:click="$set('activeTab','hero')" class="underline">Gérer dans Hero Global →</button></div>
                    @endif
                    <flux:input wire:model="contact_hero_title" label="Titre *" placeholder="Contact & Devis" />
                    <flux:input wire:model="contact_hero_badge" label="Badge" placeholder="LABORATOIRE — CONTACT ÉTUDE" />
                    <flux:textarea wire:model="contact_hero_body" label="Sous-titre" rows="3" placeholder="Formulaire à étapes..." />
                    <div class="mt-4">
                        <flux:input type="file" wire:model="contact_hero_image" label="Image de fond (5 Mo, WebP auto)" accept="image/*" />
                        @if($contact_hero_image_existing)
                            <div class="mt-2 flex items-center gap-2 rounded-lg border p-2">
                                <img src="{{ Storage::disk('public')->url($contact_hero_image_existing) }}" alt="Hero Contact" class="size-16 rounded object-cover" />
                                <div class="flex-1 text-xs text-zinc-500 truncate">{{ $contact_hero_image_existing }}</div>
                                <flux:button size="xs" variant="ghost" wire:click="removeContactImage" wire:confirm="Supprimer cette image ?">Supprimer</flux:button>
                            </div>
                        @endif
                        @if(count($media_library))
                            <div class="mt-2 flex gap-2 items-center">
                                <select wire:model="media_picker.contact_hero" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                    <option value="">— ou depuis médiathèque —</option>
                                    @foreach($media_library as $m)<option value="{{ $m['id'] }}">{{ Str::limit($m['name'],30) }}</option>@endforeach
                                </select>
                                <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('contact_hero')">Utiliser</flux:button>
                            </div>
                        @endif
                        @error('contact_hero_image') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>
                    <div wire:loading wire:target="contact_hero_image" class="text-xs text-zinc-500">Upload en cours…</div>
                </div>
                <flux:button type="submit" variant="primary">Enregistrer Contact</flux:button>
            </form>
        </div>
    @elseif($activeTab === 'hero')
        <div class="pt-6">
            <form wire:submit="saveSharedHero" class="space-y-4 max-w-3xl">
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Hero Global — Image partagée pour tous les heroes</flux:heading>
                    <flux:text class="text-sm text-zinc-500 mb-3">Cette image sera utilisée comme fond pour <strong>tous</strong> les heroes (À propos, Services, Lotissements, Réalisations, Actualités, Contact). Modifiez-la ici une seule fois — cohérence garantie.</flux:text>
                    <flux:input type="file" wire:model="shared_hero_image" label="Image Hero partagée (5 Mo, WebP/AVIF auto, 1600×900 recommandé)" accept="image/*" />
                    @if($shared_hero_image_existing)
                        <div class="mt-3 flex items-center gap-2 rounded-lg border p-2">
                            <img src="{{ Storage::disk('public')->url($shared_hero_image_existing) }}" alt="Hero partagé" class="size-16 rounded object-cover" />
                            <div class="flex-1 text-xs text-zinc-500 truncate">{{ $shared_hero_image_existing }}</div>
                            <flux:button size="xs" variant="ghost" wire:click="removeSharedHeroImage" wire:confirm="Supprimer l'image partagée ? Les heroes retomberont sur leurs images par page ou le fallback Unsplash.">Supprimer</flux:button>
                        </div>
                    @else
                        <div class="mt-3 rounded-lg bg-amber-50 p-3 text-xs text-amber-800">Aucune image partagée — les heroes utilisent leurs images par page (ou fallback Unsplash). Uploadez ici pour unifier.</div>
                    @endif
                    @if(count($media_library))
                        <div class="mt-3 flex gap-2 items-center">
                            <select wire:model="media_picker.shared_hero" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                <option value="">— ou depuis médiathèque —</option>
                                @foreach($media_library as $m)<option value="{{ $m['id'] }}">{{ \Illuminate\Support\Str::limit($m['name'],30) }}</option>@endforeach
                            </select>
                            <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('shared_hero')">Utiliser</flux:button>
                        </div>
                    @endif
                    @error('shared_hero_image') <div class="text-xs text-red-600 mt-2">{{ $message }}</div> @enderror
                    <div wire:loading wire:target="shared_hero_image" class="text-xs text-zinc-500 mt-2">Upload en cours…</div>
                </div>
                <flux:button type="submit" variant="primary">Enregistrer Hero Global</flux:button>
                <div class="text-xs text-zinc-500">Utilisé via <code>SiteSetting::get('hero.shared')['image']</code> dans <code>page-hero-simple</code> — prioritaire sur les images par page.</div>
            </form>
        </div>
    @elseif($activeTab === 'global')
        <div class="pt-6">
            <form wire:submit="saveGlobal" class="space-y-6 max-w-3xl">
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Infos Société</flux:heading>
                    <div class="grid md:grid-cols-2 gap-4">
                        <flux:input wire:model="company_name" label="Nom société *" placeholder="SIBEA-CI" />
                        <flux:input wire:model="company_siret" label="SIRET" placeholder="CI-2022-0016466 Q" />
                        <flux:input wire:model="company_capital" label="Capital" placeholder="100 000 000 FCFA" />
                        <flux:input wire:model="company_tva" label="TVA Intra" placeholder="CI00123456789" />
                    </div>
                    <div class="mt-4">
                        <flux:textarea wire:model="company_address" label="Adresse complète *" rows="2" placeholder="Abidjan, Bingerville..." />
                    </div>
                    <div class="grid md:grid-cols-3 gap-4 mt-4">
                        <flux:input wire:model="company_phone" label="Téléphone *" placeholder="+225 07 00 00 00 00" />
                        <flux:input wire:model="company_email" label="Email *" placeholder="contact@sibea-ci.ci" />
                        <flux:input wire:model="company_whatsapp" label="WhatsApp" placeholder="+225 07 00 00 00 00" />
                    </div>
                    <flux:input wire:model="company_hours" label="Horaires" placeholder="Lun-Ven 8h-17h, Sam 8h-12h" class="mt-4" />
                </div>

                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Footer</flux:heading>
                    <flux:textarea wire:model="footer_copyright" label="Copyright" rows="1" placeholder="© 2024 SIBEA-CI. Tous droits réservés." />
                    <div class="grid md:grid-cols-3 gap-4 mt-4">
                        <flux:input wire:model="footer_mentions_legales" label="Mentions légales" placeholder="Mentions légales" />
                        <flux:input wire:model="footer_cgv" label="CGV" placeholder="Conditions Générales de Vente" />
                        <flux:input wire:model="footer_confidentialite" label="Confidentialité" placeholder="Politique de confidentialité" />
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Réseaux Sociaux</flux:heading>
                    @foreach($social_networks as $i => $sn)
                        <div class="mb-3 rounded-lg bg-zinc-50 p-3">
                            <div class="grid md:grid-cols-3 gap-2">
                                <flux:input wire:model="social_networks.{{ $i }}.name" label="Nom" placeholder="Facebook" />
                                <flux:input wire:model="social_networks.{{ $i }}.url" label="URL" placeholder="https://facebook.com/..." />
                                <flux:input wire:model="social_networks.{{ $i }}.icon" label="Icône (flux)" placeholder="facebook" />
                            </div>
                            <div class="flex justify-end mt-2">
                                <flux:button size="xs" variant="danger" wire:click="removeSocialNetwork({{ $i }})" wire:confirm="Supprimer ?">Supprimer</flux:button>
                            </div>
                        </div>
                    @endforeach
                    <flux:button wire:click="addSocialNetwork" variant="ghost" icon="plus">Ajouter un réseau</flux:button>
                </div>

                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Légal & liens — Footer (éditable)</flux:heading>
                    <flux:text class="text-xs text-zinc-500 mb-3">Liens affichés dans la colonne 3 du footer. Ordre = ordre d'affichage.</flux:text>
                    @foreach($footer_links as $i => $link)
                        <div class="mb-3 rounded-lg bg-zinc-50 p-3">
                            <div class="grid md:grid-cols-2 gap-2">
                                <flux:input wire:model="footer_links.{{ $i }}.label" label="Label" placeholder="Accueil" />
                                <flux:input wire:model="footer_links.{{ $i }}.url" label="URL" placeholder="/a-propos" />
                            </div>
                            <div class="flex justify-end mt-2">
                                <flux:button size="xs" variant="danger" wire:click="removeFooterLink({{ $i }})" wire:confirm="Supprimer ?">Supprimer</flux:button>
                            </div>
                        </div>
                    @endforeach
                    <flux:button wire:click="addFooterLink" variant="ghost" icon="plus">Ajouter un lien</flux:button>
                </div>

                <flux:button type="submit" variant="primary">Enregistrer Global / Footer</flux:button>
            </form>
        </div>
    @elseif($activeTab === 'header')
        <div class="pt-6">
            <form wire:submit="saveHeader" class="space-y-6 max-w-3xl">
                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Logo Header</flux:heading>
                    <div class="mt-2">
                        <flux:input type="file" wire:model="header_logo" label="Logo (5 Mo, WebP auto)" accept="image/*" />
                        @if($header_logo_existing)
                            <div class="mt-2 flex items-center gap-2 rounded-lg border p-2">
                                <img src="{{ Storage::disk('public')->url($header_logo_existing) }}" alt="Logo Header" class="size-16 rounded object-cover" />
                                <div class="flex-1 text-xs text-zinc-500 truncate">{{ $header_logo_existing }}</div>
                                <flux:button size="xs" variant="ghost" wire:click="removeHeaderLogo" wire:confirm="Supprimer le logo ?">Supprimer</flux:button>
                            </div>
                        @endif
                        @error('header_logo') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                        @if(count($media_library))
                            <div class="mt-2 flex gap-2 items-center">
                                <select wire:model="media_picker.header_logo" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                    <option value="">— ou depuis médiathèque —</option>
                                    @foreach($media_library as $m)<option value="{{ $m['id'] }}">{{ Str::limit($m['name'],30) }}</option>@endforeach
                                </select>
                                <flux:button size="xs" variant="ghost" wire:click="applyPickedMedia('header_logo')">Utiliser</flux:button>
                            </div>
                        @endif
                    </div>
                    <div wire:loading wire:target="header_logo" class="text-xs text-zinc-500">Upload en cours…</div>
                </div>

                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Navigation — Menu Principal</flux:heading>
                    @foreach($menu_items as $i => $item)
                        <div class="mb-3 rounded-lg bg-zinc-50 p-3">
                            <div class="grid md:grid-cols-[1fr_1fr_80px_60px] gap-2 items-end">
                                <flux:input wire:model="menu_items.{{ $i }}.label" label="Label" placeholder="Accueil" />
                                <flux:input wire:model="menu_items.{{ $i }}.url" label="URL" placeholder="/a-propos" />
                                <flux:input wire:model="menu_items.{{ $i }}.order" type="number" label="Ordre" min="1" max="15" />
                                <div class="flex gap-1">
                                    <flux:button size="xs" variant="ghost" :disabled="$i===0" wire:click="moveMenuItemUp({{ $i }})" title="Monter">↑</flux:button>
                                    <flux:button size="xs" variant="ghost" :disabled="$i===count($menu_items)-1" wire:click="moveMenuItemDown({{ $i }})" title="Descendre">↓</flux:button>
                                    <flux:button size="xs" variant="danger" wire:click="removeMenuItem({{ $i }})" wire:confirm="Supprimer ?">Supprimer</flux:button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <flux:button wire:click="addMenuItem" variant="ghost" icon="plus">Ajouter un lien</flux:button>
                </div>

                <div class="rounded-2xl border border-zinc-200 p-4">
                    <flux:heading size="sm" class="mb-3">Contact Header</flux:heading>
                    <div class="grid md:grid-cols-3 gap-4">
                        <flux:input wire:model="header_phone" label="Téléphone" placeholder="+225 07 00 00 00 00" />
                        <flux:input wire:model="header_email" label="Email" placeholder="contact@sibea-ci.ci" />
                        <flux:input wire:model="header_whatsapp" label="WhatsApp" placeholder="+225 07 00 00 00 00" />
                    </div>
                    <div class="grid md:grid-cols-2 gap-4 mt-4">
                        <flux:input wire:model="header_cta_text" label="Texte CTA" placeholder="DEMANDER UN DEVIS" />
                        <flux:input wire:model="header_cta_url" label="URL CTA" placeholder="/contact" />
                    </div>
                </div>

                <flux:button type="submit" variant="primary">Enregistrer Header / Navigation</flux:button>
            </form>
        </div>
    @elseif($activeTab === 'media')
        <div class="pt-6 space-y-6">
            <div class="rounded-2xl border border-zinc-200 p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <flux:heading size="sm">Bibliothèque Médias</flux:heading>
                        <flux:text class="text-sm text-zinc-500">Centralisez les images — glissez depuis ici vers n'importe quel Hero/Bannière/Service/Équipe en 1 clic. Optimisé WebP/AVIF auto.</flux:text>
                        <div class="mt-2 text-xs text-zinc-500">{{ count($media_library) }}/100 images · {{ count($this->filteredMediaLibrary) }} affichée(s) @if($media_search) pour "{{ $media_search }}" @endif</div>
                    </div>
                    <div class="text-xs text-zinc-500">Stockage: <code>cms/media/</code> · max 5Mo/image</div>
                </div>
                <div class="mt-4 grid md:grid-cols-[1fr_auto] gap-3 items-end">
                    <flux:input type="file" wire:model="media_upload" label="Ajouter image(s) — sélection multiple (5 Mo, WebP/AVIF auto)" accept="image/*" multiple />
                    <flux:button variant="ghost" size="sm" wire:click="storeMedia" :disabled="!$media_upload">Enregistrer</flux:button>
                </div>
                <div wire:loading wire:target="media_upload" class="text-xs text-amber-600 mt-2">Upload + optimisation en cours…</div>
                @error('media_upload') <div class="text-xs text-red-600 mt-2">{{ $message }}</div> @enderror
                @error('media_library') <div class="text-xs text-red-600 mt-2">{{ $message }}</div> @enderror
                <div class="mt-3 flex gap-2">
                    <flux:input wire:model.live.debounce.300ms="media_search" placeholder="Rechercher par nom…" class="max-w-xs" />
                    @if($media_search)<flux:button size="xs" variant="ghost" wire:click="$set('media_search','')">Effacer</flux:button>@endif
                </div>
                <div class="mt-3 rounded-lg bg-zinc-50 p-3 text-xs text-zinc-600">💡 Astuce : cliquez <strong>Copier URL</strong> pour coller dans un article, ou <strong>Utiliser comme…</strong> pour appliquer directement à un Hero/Banner/Logo sans re-upload. Suppression nettoie aussi les variantes WebP/AVIF.</div>
            </div>

            <div>
                <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @forelse($this->filteredMediaLibrary as $m)
                        @php $url = Storage::disk('public')->url($m['path']); @endphp
                        <div class="group relative flex flex-col rounded-2xl border border-zinc-200 overflow-hidden bg-white">
                            <a href="{{ $url }}" target="_blank" class="block relative">
                                <img src="{{ $url }}" alt="{{ $m['name'] }}" class="w-full aspect-square object-cover group-hover:scale-[1.02] transition" loading="lazy" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-80"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-2">
                                    <div class="text-xs font-medium text-white truncate" title="{{ $m['name'] }}">{{ $m['name'] }}</div>
                                    <div class="text-[10px] text-white/70 truncate" title="{{ $m['path'] }}">{{ $m['path'] }}</div>
                                </div>
                                <div class="absolute top-2 left-2 rounded bg-black/60 px-1.5 py-0.5 text-[10px] text-white">{{ $m['created_at'] ?? '' }}</div>
                            </a>
                            <div class="p-2 space-y-2">
                                <div class="flex gap-1">
                                    <a href="{{ $url }}" target="_blank" class="flex-1 inline-flex items-center justify-center rounded-lg border border-zinc-200 px-2 py-1.5 text-xs font-medium hover:bg-zinc-50">Voir</a>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $url }}'); this.textContent='Copié!'; setTimeout(()=>this.textContent='Copier URL',1500)" class="flex-1 inline-flex items-center justify-center rounded-lg bg-zinc-900 px-2 py-1.5 text-xs font-medium text-white hover:bg-zinc-800">Copier URL</button>
                                </div>
                                <div class="flex gap-1 items-center">
                                    <select id="target-{{ $m['id'] }}" class="flex-1 rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-xs">
                                        <option value="hero_slide1">Hero Slide 1</option>
                                        <option value="hero_slide2">Hero Slide 2</option>
                                        <option value="banner">Bannière Accueil</option>
                                        <option value="about">À propos</option>
                                        <option value="services_hero">Hero Services</option>
                                        <option value="programs_hero">Hero Lotissements</option>
                                        <option value="projects_hero">Hero Réalisations</option>
                                        <option value="posts_hero">Hero Actualités</option>
                                        <option value="contact_hero">Hero Contact</option>
                                        <option value="header_logo">Logo Header</option>
                                        <option value="seo_og">SEO OG Image</option>
                                        @foreach($team_members as $ti => $tm)<option value="team:{{ $ti }}">Équipe #{{ $ti+1 }} — {{ Str::limit($tm['name']??'Membre',15) }}</option>@endforeach
                                        @foreach($services as $si => $svc)<option value="service:{{ $si }}">Service #{{ $si+1 }} — {{ Str::limit($svc['key']??'svc',12) }}</option>@endforeach
                                    </select>
                                    <button type="button" onclick="let s=document.getElementById('target-{{ $m['id'] }}').value; $wire.applyMedia('{{ $m['id'] }}', s)" class="inline-flex items-center justify-center rounded-lg bg-primary px-3 py-1.5 text-xs font-medium text-white hover:bg-primary/90">Appliquer</button>
                                </div>
                                <div class="flex gap-1">
                                    <flux:button size="xs" variant="ghost" class="flex-1" wire:click="removeMedia('{{ $m['id'] }}')" wire:confirm="Supprimer ce média ? Le fichier et ses variantes WebP/AVIF seront effacés.">🗑️ Supprimer</flux:button>
                                </div>
                                <div class="text-[10px] text-zinc-500 break-all select-all">{{ $url }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full border border-dashed rounded-xl p-8 text-center">
                            <div class="text-sm font-medium text-zinc-700">Aucun média @if($media_search) pour "{{ $media_search }}" @endif</div>
                            <div class="text-xs text-zinc-500 mt-1">Déposez des images ci-dessus — elles apparaîtront ici et seront réutilisables en 1 clic dans tout le CMS.</div>
                        </div>
                    @endforelse
                </div>
                @if(count($media_library) > 20)
                    <div class="mt-4 text-xs text-zinc-500 text-center">{{ count($this->filteredMediaLibrary) }} / {{ count($media_library) }} · utilisez la recherche pour filtrer.</div>
                @endif
            </div>
        </div>
    @endif
</section>
