<template>
  <div class="min-h-screen bg-slate-50">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-center h-16">

          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-600 flex items-center justify-center text-white font-bold">
              C
            </div>
            <div>
              <h1 class="font-bold text-lg text-slate-900">CiviSense</h1>
              <p class="text-xs text-gray-500">
                Smart Civic. Stronger Communities.
              </p>
            </div>
          </div>

          <div class="flex gap-3">
            <button
              class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50"
            >
              Login
            </button>

            <button
              class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700"
            >
              Submit Complaint
            </button>
          </div>

        </div>
      </div>
    </nav>

    <!-- Hero -->
    <section class="max-w-7xl mx-auto px-6 py-20">
      <div class="grid lg:grid-cols-2 gap-12 items-center">

        <div>
          <span
            class="inline-block px-4 py-2 rounded-full bg-green-100 text-green-700 text-sm font-semibold"
          >
            AI-Powered Civic Reporting
          </span>

          <h2
            class="text-5xl lg:text-6xl font-black text-slate-900 mt-6 leading-tight"
          >
            Report Issues.<br />
            Track Progress.<br />
            Build Better Communities.
          </h2>

          <p class="mt-6 text-lg text-gray-600">
            Report road damage, garbage issues, water leakage, broken street
            lights and more. Our AI automatically categorizes complaints and
            routes them to the correct authority.
          </p>

          <div class="mt-8 flex flex-wrap gap-4">
            <button
              class="bg-green-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-green-700"
            >
              Get Started
            </button>

            <button
              class="border border-gray-300 px-6 py-3 rounded-xl font-semibold hover:bg-gray-50"
            >
              Learn More
            </button>
          </div>
        </div>

        <div class="flex justify-center">
          <div class="bg-white rounded-3xl shadow-2xl p-6 w-full max-w-sm">

            <div
              class="h-48 rounded-2xl bg-gradient-to-br from-green-100 to-blue-100 flex items-center justify-center"
            >
              <span class="text-7xl">📍</span>
            </div>

            <div class="mt-6">
              <h3 class="font-bold text-lg">
                Broken Street Light
              </h3>

              <p class="text-sm text-gray-500 mt-1">
                Ward 5, Kathmandu
              </p>

              <div class="mt-4">
                <div class="flex justify-between text-sm">
                  <span>Status</span>
                  <span class="font-semibold text-orange-500">
                    In Progress
                  </span>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                  <div
                    class="bg-orange-500 h-2 rounded-full"
                    style="width:60%"
                  ></div>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </section>

    <!-- Features -->
    <section class="bg-white py-20">
      <div class="max-w-7xl mx-auto px-6">

        <div class="text-center mb-12">
          <h2 class="text-4xl font-black text-slate-900">
            Platform Features
          </h2>

          <p class="text-gray-500 mt-3">
            Everything needed to manage civic issues efficiently.
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

          <div
            v-for="feature in features"
            :key="feature.title"
            class="bg-slate-50 rounded-2xl p-6 border hover:shadow-lg transition"
          >
            <div class="text-4xl mb-4">
              {{ feature.icon }}
            </div>

            <h3 class="font-bold text-lg">
              {{ feature.title }}
            </h3>

            <p class="text-gray-600 mt-2">
              {{ feature.desc }}
            </p>
          </div>

        </div>

      </div>
    </section>

    <!-- Statistics -->
    <section class="py-20">
      <div class="max-w-7xl mx-auto px-6">

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">

          <div
            v-for="stat in stats"
            :key="stat.label"
            class="bg-white rounded-2xl shadow p-6 text-center"
          >
            <h3 class="text-4xl font-black text-green-600">
              {{ stat.value }}
            </h3>

            <p class="font-semibold mt-2">
              {{ stat.label }}
            </p>

            <p class="text-sm text-gray-500">
              {{ stat.sub }}
            </p>
          </div>

        </div>

      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white py-10">
      <div class="max-w-7xl mx-auto px-6 text-center">
        <h3 class="font-bold text-xl">
          CiviSense
        </h3>

        <p class="text-gray-400 mt-2">
          Smart Civic. Stronger Communities.
        </p>

        <p class="text-gray-500 text-sm mt-4">
          © {{ currentYear }} CiviSense. All rights reserved.
        </p>
      </div>
    </footer>

  </div>
</template>

<script setup>
const currentYear = new Date().getFullYear();

const features = [
  {
    icon: "🤖",
    title: "AI Categorization",
    desc: "Automatically categorizes complaints."
  },
  {
    icon: "📍",
    title: "GPS Location",
    desc: "Capture exact complaint location."
  },
  {
    icon: "📡",
    title: "Live Tracking",
    desc: "Track complaint progress in real-time."
  },
  {
    icon: "🔔",
    title: "Notifications",
    desc: "Receive updates via alerts."
  },
  {
    icon: "📊",
    title: "Analytics Dashboard",
    desc: "Visualize complaint trends."
  },
  {
    icon: "🔒",
    title: "Secure Platform",
    desc: "Safe and reliable reporting."
  }
];

const stats = [
  {
    value: "12,000+",
    label: "Complaints",
    sub: "Filed successfully"
  },
  {
    value: "95%",
    label: "Resolved",
    sub: "Issues resolved"
  },
  {
    value: "50+",
    label: "Municipalities",
    sub: "Using the platform"
  },
  {
    value: "150K",
    label: "Citizens",
    sub: "Active users"
  }
];
</script>
```
