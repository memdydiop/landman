<div class="rounded-2xl border border-zinc-200 bg-white p-6">
    @if($sent)
        <div class="text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">✓</div>
            <h3 class="mt-4 text-lg font-semibold">Demande envoyée !</h3>
            <p class="mt-2 text-sm text-zinc-600">Merci {{ $name ?: '' }} — un conseiller vous recontacte sous 24h. Référence #{{ $createdInquiryId }}.</p>
            @if($plot_id && $this->selectedPlot && $this->selectedPlot->plan_pdf_path)
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($this->selectedPlot->plan_pdf_path) }}" target="_blank" class="mt-4 inline-flex rounded-full bg-[#003366] px-6 py-2 text-sm font-semibold text-white hover:bg-[#002244]">Télécharger le plan PDF</a>
            @endif
            <div class="mt-4 flex justify-center gap-3">
                <button wire:click="resetWizard" class="text-sm font-medium text-zinc-600 hover:underline">Nouvelle demande</button>
                <a href="https://wa.me/2250700000000" target="_blank" class="rounded-full bg-emerald-600 p-2.5 text-white hover:bg-emerald-700 inline-flex items-center justify-center" aria-label="WhatsApp"><svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2a10 10 0 0 0-8.8 14.82L2 22l5.34-1.4A10 10 0 1 0 12.04 2Zm0 17.82a7.82 7.82 0 0 1-3.99-1.09l-.29-.17-3.18.83.84-3.1-.19-.32a7.82 7.82 0 0 1-1.18-4.15A7.83 7.83 0 0 1 12.04 4a7.83 7.83 0 0 1 7.83 7.82 7.83 7.83 0 0 1-7.83 7.99Zm4.49-5.68c-.25-.12-1.46-.72-1.69-.8-.22-.08-.39-.12-.55.12-.16.25-.63.8-.78.96-.14.17-.29.19-.53.06-.25-.12-1.04-.38-1.99-1.22-.73-.65-1.23-1.46-1.37-1.71-.14-.25-.02-.38.11-.51.11-.11.25-.28.37-.42.12-.14.16-.25.25-.41.08-.16.04-.3-.02-.42-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47c-.16 0-.43.06-.65.3-.22.25-.85.83-.85 2.02 0 1.19.87 2.34 1 2.5.12.16 1.73 2.64 4.2 3.71.59.25 1.04.4 1.4.51.59.19 1.12.16 1.54.1.47-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.23-.18-.47-.3Z"/></svg></a>
            </div>
        </div>
    @else
        <!-- Progress -->
        <div class="mb-6 flex items-center gap-2">
            @for($i=1; $i<=3; $i++)
                <div class="flex items-center gap-2">
                    <div class="flex size-7 items-center justify-center rounded-full text-xs font-bold {{ $step >= $i ? 'bg-[#003366] text-white' : 'bg-zinc-200 text-zinc-600' }}">{{ $i }}</div>
                    @if($i < 3)<div class="h-px w-8 bg-zinc-200"></div>@endif
                </div>
            @endfor
            <span class="ml-2 text-xs text-zinc-500">Étape {{ $step }}/3</span>
        </div>

        <!-- Step 1: Besoin -->
        @if($step === 1)
            <h3 class="font-semibold">Quel est votre besoin ?</h3>
            <p class="text-sm text-zinc-600">Choisissez l'expertise visée — le formulaire s'adapte.</p>
            <div class="mt-4 grid gap-3">
                @foreach(\App\Enums\InquiryType::cases() as $type)
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 hover:bg-zinc-50 {{ $inquiry_type === $type->value ? 'border-[#f0f4f8]0 bg-[#f0f4f8]' : 'border-zinc-200' }}">
                        <input type="radio" wire:model.live="inquiry_type" value="{{ $type->value }}" class="text-[#003366]" />
                        <div>
                            <div class="text-sm font-medium">{{ $type->label() }}</div>
                            <div class="text-xs text-zinc-500">
                                @if($type->value === 'devis_btp') Construction, VRD, gros œuvre
                                @elseif($type->value === 'achat_lot') Réservation terrain viabilisé
                                @elseif($type->value === 'partenariat') Promoteur, collectivité, architecte
                                @else Question générale @endif
                            </div>
                        </div>
                    </label>
                @endforeach
                @error('inquiry_type') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="mt-4">
                <label class="text-sm font-medium">Expertise (optionnel)</label>
                <select wire:model="service_type" class="mt-1 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm">
                    <option value="">— Sélectionner —</option>
                    @foreach(\App\Enums\ServiceType::cases() as $s) <option value="{{ $s->value }}">{{ $s->label() }}</option> @endforeach
                </select>
                @error('service_type') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>

            <div class="mt-6 flex justify-end">
                <button wire:click="next" class="rounded-full bg-[#003366] px-6 py-2 text-sm font-semibold text-white hover:bg-[#002244]">Continuer →</button>
            </div>
        @endif

        <!-- Step 2: Détails contextuels -->
        @if($step === 2)
            <h3 class="font-semibold">Précisez votre projet</h3>
            <p class="text-xs text-zinc-500">Complétez les informations adaptées à votre projet SIBEA-CI.</p>

            @if($inquiry_type === \App\Enums\InquiryType::DEVIS_BTP->value)
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">
                        <span>Type de projet *</span>
                        <select wire:model="project_type" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2">
                            <option value="">— Sélectionnez —</option>
                            <option value="Résidentiel">Résidentiel</option>
                            <option value="Commercial">Commercial</option>
                            <option value="Industriel">Industriel</option>
                            <option value="Travaux publics">Travaux publics</option>
                            <option value="Rénovation">Rénovation</option>
                            <option value="Autre">Autre</option>
                        </select>
                        @error('project_type') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-sm">
                        <span>Taille du projet</span>
                        <select wire:model="project_size" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2">
                            <option value="">— Sélectionnez —</option>
                            <option value="Petit (< 500m²)">Petit (&lt; 500m²)</option>
                            <option value="Moyen (500m² - 2000m²)">Moyen (500m² - 2000m²)</option>
                            <option value="Grand (2000m² - 5000m²)">Grand (2000m² - 5000m²)</option>
                            <option value="Très grand (> 5000m²)">Très grand (&gt; 5000m²)</option>
                        </select>
                    </label>
                    <label class="text-sm">
                        <span>Localisation</span>
                        <input wire:model="location" placeholder="Abidjan, Bouaké..." class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2" />
                        @error('location') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-sm">
                        <span>Surface souhaitée (m²)</span>
                        <input wire:model="surface_wanted" type="number" step="0.01" placeholder="350" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2" />
                    </label>
                </div>
                <div class="mt-4">
                    <span class="text-sm font-medium">Services nécessaires *</span>
                    <div class="mt-2 grid gap-2 md:grid-cols-2">
                        @foreach(['Génie Civil','Lotissement et aménagement','Conception','Rénovation','Gestion de projet'] as $svc)
                            <label class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm {{ in_array($svc, $services_needed) ? 'bg-[#f0f4f8] border-[#003366]' : 'bg-white' }}">
                                <input type="checkbox" value="{{ $svc }}" wire:model.live="services_needed" class="rounded text-[#003366]" />
                                <span>{{ $svc }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('services_needed') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">
                        <span>Délai souhaité</span>
                        <select wire:model="deadline" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2">
                            <option value="">— Sélectionnez —</option>
                            <option value="Urgent (< 3 mois)">Urgent (&lt; 3 mois)</option>
                            <option value="Dans les 6 mois">Dans les 6 mois</option>
                            <option value="Flexible (6-12 mois)">Flexible (6-12 mois)</option>
                            <option value="Planification future (> 1 an)">Planification future (&gt; 1 an)</option>
                        </select>
                    </label>
                    <label class="text-sm">
                        <span>Budget estimé (FCFA)</span>
                        <select wire:model="budget_range" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2">
                            <option value="">— Sélectionnez —</option>
                            <option value="10 - 50 millions">10 - 50 millions</option>
                            <option value="50 - 100 millions">50 - 100 millions</option>
                            <option value="100 - 500 millions">100 - 500 millions</option>
                            <option value="Plus de 500 millions">Plus de 500 millions</option>
                            <option value="À déterminer">À déterminer</option>
                        </select>
                        @error('budget_range') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">
                        <span>Budget précis (optionnel)</span>
                        <input wire:model="budget" type="number" step="0.01" placeholder="15000000" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2" />
                    </label>
                    <label class="text-sm">
                        <span>Service principal</span>
                        <select wire:model="service_type" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2">
                            <option value="{{ \App\Enums\ServiceType::BTP->value }}">BTP</option>
                            <option value="{{ \App\Enums\ServiceType::AMENAGEMENT->value }}">Aménagement VRD</option>
                            <option value="{{ \App\Enums\ServiceType::LOTISSEMENT->value }}">Pétrole & Énergie</option>
                            <option value="{{ \App\Enums\ServiceType::RENOVATION->value }}">Agro-industrie</option>
                        </select>
                    </label>
                </div>
            @elseif($inquiry_type === \App\Enums\InquiryType::ACHAT_LOT->value)
                <div class="mt-4 space-y-4">
                    <label class="text-sm">
                        <span>Programme *</span>
                        <select wire:model.live="program_id" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2">
                            <option value="">— Choisir un programme —</option>
                            @foreach($this->programs as $p) <option value="{{ $p->id }}">{{ $p->title }} — {{ $p->city }} ({{ $p->available_plots_count ?? 0 }} dispo)</option> @endforeach
                        </select>
                        @error('program_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    @if($program_id)
                        <label class="text-sm">
                            <span>Lot (optionnel)</span>
                            <select wire:model.live="plot_id" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2">
                                <option value="">— Tous lots ou choisir —</option>
                                @foreach($this->plotsForProgram as $plot)
                                    <option value="{{ $plot->id }}">{{ $plot->reference }} — {{ $plot->surface_m2 }} m² — {{ $plot->status->label() }} @if($plot->price) — {{ number_format((float)$plot->price,0,',',' ') }} FCFA @endif</option>
                                @endforeach
                            </select>
                        </label>
                        @if($this->selectedPlot)
                            <div class="rounded-xl bg-zinc-50 p-3 text-xs">
                                Lot <span class="font-mono font-bold">{{ $this->selectedPlot->reference }}</span> — {{ $this->selectedPlot->surface_m2 }} m² — {{ $this->selectedPlot->status->label() }}
                                @if($this->selectedPlot->plan_pdf_path) <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($this->selectedPlot->plan_pdf_path) }}" target="_blank" class="ml-2 text-[#003366] underline">Voir plan PDF</a> @endif
                            </div>
                        @endif
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="text-sm">
                            <span>Taille du projet</span>
                            <select wire:model="project_size" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2">
                                <option value="">— Sélectionnez —</option>
                                <option value="Petit (< 500m²)">Petit (&lt; 500m²)</option>
                                <option value="Moyen (500m² - 2000m²)">Moyen</option>
                                <option value="Grand (2000m² - 5000m²)">Grand</option>
                                <option value="Très grand (> 5000m²)">Très grand</option>
                            </select>
                        </label>
                        <label class="text-sm">
                            <span>Budget estimé</span>
                            <select wire:model="budget_range" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2">
                                <option value="">— Sélectionnez —</option>
                                <option value="10 - 50 millions">10 - 50 millions</option>
                                <option value="50 - 100 millions">50 - 100 millions</option>
                                <option value="100 - 500 millions">100 - 500 millions</option>
                                <option value="Plus de 500 millions">Plus de 500 millions</option>
                                <option value="À déterminer">À déterminer</option>
                            </select>
                        </label>
                    </div>
                    <label class="text-sm">
                        <span>Budget précis (FCFA)</span>
                        <input wire:model="budget" type="number" placeholder="20000000" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2" />
                    </label>
                </div>
            @else
                <p class="mt-2 text-sm text-zinc-600">Décrivez votre besoin à l'étape suivante — partenariat ou contact général.</p>
            @endif

            <div class="mt-6 flex justify-between">
                <button wire:click="back" class="rounded-full border border-zinc-300 px-6 py-2 text-sm">← Retour</button>
                <button wire:click="next" class="rounded-full bg-[#003366] px-6 py-2 text-sm font-semibold text-white hover:bg-[#002244]">Continuer →</button>
            </div>
        @endif

        <!-- Step 3: Coordonnées -->
        @if($step === 3)
            <h3 class="font-semibold">Vos coordonnées</h3>
            <p class="text-xs text-zinc-500">Prénom/Nom, entreprise optionnelle.</p>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label class="text-sm">
                    <span>Prénom *</span>
                    <input wire:model="first_name" placeholder="Jean" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2" />
                    @error('first_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="text-sm">
                    <span>Nom *</span>
                    <input wire:model="last_name" placeholder="Kouassi" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2" />
                    @error('last_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label class="text-sm">
                    <span>Nom complet (auto)</span>
                    <input wire:model="name" placeholder="Jean Kouassi" class="mt-1 w-full rounded-xl border border-zinc-300 bg-zinc-50 px-3 py-2" readonly />
                    @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="text-sm">
                    <span>Entreprise</span>
                    <input wire:model="company" placeholder="SARL ..." class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2" />
                    @error('company') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label class="text-sm">
                    <span>Téléphone *</span>
                    <input wire:model="phone" placeholder="+225 ..." class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2" />
                    @error('phone') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="text-sm">
                    <span>Email *</span>
                    <input wire:model="email" type="email" placeholder="vous@exemple.com" class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2" />
                    @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
            </div>
            <label class="mt-4 block text-sm">
                <span>Description de votre projet *</span>
                <textarea wire:model="message" rows="4" placeholder="Détails, surface, budget, délais..." class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2"></textarea>
                @error('message') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>
            <label class="mt-4 flex items-center gap-2 text-xs">
                <input type="checkbox" wire:model="rgpd" class="rounded" />
                <span>J'accepte la politique de traitement des données (RGPD) *</span>
            </label>
            @error('rgpd') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            <!-- honeypot -->
            <input wire:model="website" type="text" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />
            @error('website') <span class="text-xs text-red-600">{{ $message }}</span> @enderror

            <div class="mt-6 flex justify-between">
                <button wire:click="back" class="rounded-full border border-zinc-300 px-6 py-2 text-sm">← Retour</button>
                <button wire:click="submit" class="rounded-full bg-[#003366] px-8 py-3 text-sm font-semibold text-white hover:bg-[#002244]">Envoyer la demande</button>
            </div>
            <p class="mt-3 text-xs text-zinc-500">Réponse sous 24h · Données non partagées · Plan PDF envoyé après validation.</p>
        @endif
    @endif
</div>
