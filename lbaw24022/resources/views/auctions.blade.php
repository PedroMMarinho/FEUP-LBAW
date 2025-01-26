<x-app-layout>
    <div class="py-12">
        <x-auctions-search-and-filters 
            :search="$search"
            :location="$location"
            :categories="$categories"
            :selected-category="$selectedCategory"
            :from-bid="$fromBid"
            :to-bid="$toBid"
            :total-auctions="$totalAuctions"
            :auctions="$auctions"
        />
    </div>
</x-app-layout>