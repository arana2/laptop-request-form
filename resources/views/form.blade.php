<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <title>Computer Hardware Request Form</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-[#7A003C] text-white p-4 shadow">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="/images/eng-mcmaster.jpg" alt="McMaster Engineering Logo" class="w-11 h-11 object-contain">
                <div>
                    <h1 class="text-lg font-semibold">
                        Faculty of Engineering
                    </h1>
                    <p class="text-sm opacity-80">
                        Computer Hardware Request Form
                    </p>
                </div>
            </div>
        </div>
    </header>
    <!-- Content -->
    <main class="max-w-4xl mx-auto mt-10 bg-white p-6 md:p-8 rounded shadow">
        <form id="requestForm" class="space-y-8">
            <section>
                <!-- Requester Information -->
                <h3 class="text-lg font-semibold mb-4">Requester Information</h3>
                <!-- Who is this for -->
                <div class="mb-4">
                    <label class="block font-medium mb-2">
                            Who is this request for? <span class="text-red-500">*</span>
                    </label>

                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="request_for" value="self" required>
                            Myself
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="radio" name="request_for" value="other" required>
                            Someone else
                        </label>
                    </div>
                </div>

                <!-- Requester fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="requester_name" required class="form-input">
                    </div>

                    <div>
                        <label class="block text-sm mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="requester_email" required class="form-input">
                    </div>
                </div>
            </section>

            <!-- Recipient Information -->
            <section id="recipientSection" class="hidden">
                <h3 class="text-lg font-semibold mb-4">Recipient Information</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">
                            Recipient Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="recipient_name" name="recipient_name" class="form-input">
                    </div>

                    <div>
                        <label class="block text-sm mb-1">
                            Recipient Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="recipient_email" name="recipient_email" class="form-input">
                    </div>
                </div>
            </section>

            <div class="pt-4 flex justify-end">
                <button
                    id="submitBtn"
                    type="submit"
                    class="bg-[#7A003C] text-white px-6 py-2 rounded opacity-50 cursor-not-allowed"
                >
                Submit Request
                </button>
            </div>
        </form>
    </main>
</body>
</html>