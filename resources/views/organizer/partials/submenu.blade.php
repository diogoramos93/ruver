<div class="mb-6 bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-200">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8 px-6 overflow-x-auto" aria-label="Tabs">

            <a href="{{ route('organizer.events.edit', $event->id) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 text-sm transition {{ request()->routeIs('organizer.events.edit') ? 'border-black text-black font-bold' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300 font-medium' }}">
                Dados do Evento
            </a>

            <a href="{{ route('organizer.events.categories.index', $event->id) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 text-sm transition {{ request()->routeIs('organizer.events.categories.index') ? 'border-black text-black font-bold' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300 font-medium' }}">
                Categorias
            </a>

            <a href="{{ route('organizer.events.tickets.index', $event->id) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 text-sm transition {{ request()->routeIs('organizer.events.tickets.*') ? 'border-black text-black font-bold' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300 font-medium' }}">
                Ingressos
            </a>

            <!-- FORMULÁRIOS DO EVENTO -->
            <a href="{{ route('organizer.events.forms.index', $event->id) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 text-sm transition {{ request()->routeIs('organizer.events.forms.*') ? 'border-black text-black font-bold' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300 font-medium' }}">
                Formulários
            </a>

            <a href="{{ route('organizer.events.coupons.index', $event->id) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 text-sm transition {{ request()->routeIs('organizer.events.coupons.*') ? 'border-black text-black font-bold' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300 font-medium' }}">
                Cupons
            </a>

            <a href="{{ route('organizer.events.participants.index', $event->id) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 text-sm transition {{ request()->routeIs('organizer.events.participants.*') ? 'border-black text-black font-bold' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300 font-medium' }}">
                Participantes
            </a>

            <a href="{{ route('organizer.events.financial', $event->id) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 text-sm transition {{ request()->routeIs('organizer.events.financial') ? 'border-black text-black font-bold' : 'border-transparent text-gray-500 hover:text-gray-900 hover:border-gray-300 font-medium' }}">
                Financeiro
            </a>

        </nav>
    </div>
</div>
