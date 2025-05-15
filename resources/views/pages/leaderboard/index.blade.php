<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicare Sales Dashboard</title>
    <!-- Include Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      // Optional: You can customize Tailwind here if needed
      // tailwind.config = {
      //   theme: {
      //     extend: {
      //       colors: {
      //         clifford: '#da373d',
      //       }
      //     }
      //   }
      // }
    </script>
    <style>
        /* Add custom styles if absolutely necessary, but prefer Tailwind utilities */
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-6xl mx-auto">
        <!-- Main Title -->
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8">
            Medicare Sales - {{ \Carbon\Carbon::now()->format('F jS, Y') }}
        </h1>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

            <!-- Left Column: Daily Sales Leaderboard -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6">
                <h2 class="flex items-center text-xl font-semibold text-gray-700 mb-6">
                    <span class="text-2xl mr-3">🔥</span>
                    Daily Sales Leaderboard: Total count: <span class="ml-1 font-bold"><?= $today_total_deals ?></span>
                </h2>
                <div class="space-y-6">
                    @forelse ($dailyLeaders as $leader)
                        <div class="flex items-center space-x-4">
                            {{-- Placeholder image - replace with dynamic logic if needed --}}
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($leader->agent ?? 'UA') }}&size=48&background=EFEFEF&color=AAAAAA&rounded=true" alt="Maria Gonzalez" class="w-12 h-12 rounded-full object-cover flex-shrink-0" alt="{{ $leader->agent ?? 'N/A' }}" class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                            <div class="flex-grow min-w-0">
                                <p class="font-medium text-gray-800 truncate">{{ $leader->agent ?? 'Unknown Agent' }}</p>
                                {{-- Progress bar width needs logic - base it on max value? Or is it fixed?
                                     For now, just showing it. You might need more complex logic here. --}}
                                <div class="bg-gray-200 rounded-full h-2 mt-1 overflow-hidden">
                                     {{-- Example: Calculate width based on max daily sales? --}}
                                     @php
                                         $maxDaily = $dailyLeaders->max('customer_count') ?: 1; // Avoid division by zero
                                         $widthPercent = ($leader->customer_count / $maxDaily) * 100;
                                     @endphp
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $widthPercent }}%;"></div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 w-16">
                                <span class="font-medium text-gray-600">{{ $leader->customer_count }} Sales</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">No sales recorded today.</p>
                    @endforelse
                </div>
            </div>

            <!-- Right Column: Weekly & Monthly Leaders -->
            <div class="lg:col-span-1 flex flex-col gap-6 lg:gap-8">

                <!-- Weekly Leaders Card -->
<div class="bg-white rounded-xl shadow-md p-6">
    <h2 class="flex items-center text-xl font-semibold text-gray-700 mb-4">
        <span class="text-2xl mr-3">🥈</span>
        Weekly Leaders
    </h2>
    <div class="space-y-2 text-sm">
        @forelse ($weeklyLeaders as $leader)
            <div class="flex justify-between items-center py-1">
                <span class="text-gray-700 truncate">{{ $leader->agent ?? 'Unknown Agent' }}</span>
                <span class="font-semibold text-gray-800 text-right flex-shrink-0 ml-2">{{ $leader->customer_count }} Sales</span>
            </div>
        @empty
             <p class="text-gray-500 text-xs">No sales recorded this week yet.</p>
        @endforelse
    </div>
</div>

<!-- Monthly Leaders Card (Similar loop structure) -->
<div class="bg-white rounded-xl shadow-md p-6">
    <h2 class="flex items-center text-xl font-semibold text-gray-700 mb-4">
        <span class="text-2xl mr-3">🏆</span>
        Monthly Leaders
    </h2>
     <div class="space-y-2 text-sm">
         @forelse ($monthlyLeaders as $leader)
            <div class="flex justify-between items-center py-1">
                <span class="text-gray-700 truncate">{{ $leader->agent ?? 'Unknown Agent' }}</span>
                <span class="font-semibold text-gray-800 text-right flex-shrink-0 ml-2">{{ $leader->customer_count }} Sales</span>
            </div>
         @empty
              <p class="text-gray-500 text-xs">No sales recorded this month yet.</p>
         @endforelse
    </div>
</div>

            </div>

        </div> <!-- End Grid -->

    </div> <!-- End Max Width Container -->

</body>
</html>
