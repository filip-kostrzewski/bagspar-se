@switch($stage)
    @case('received') Mottagen @break
    @case('scraping') Skrapning @break
    @case('repair') Trälagning @break
    @case('glazing') Glasning @break
    @case('painting') Målning @break
    @case('ready') Klar @break
    @default Mottagen
@endswitch
