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
            <section class="form-section">
                <!-- Requester Information -->
                <h3 class="text-lg font-semibold mb-4">Requester Information</h3>
                <!-- Who is this for -->
                <div class="mb-4">
                    <label class="form-label">
                            Who is this request for? <span class="text-red-500">*</span>
                    </label>

                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="radio" class="form-radio" name="request_for" value="self" required>
                            Myself
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="radio" class="form-radio" name="request_for" value="other" required>
                            Someone else
                        </label>
                    </div>
                </div>

                <!-- Requester fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="requester_name" required class="form-input">
                    </div>

                    <div>
                        <label class="form-label">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="requester_email" required class="form-input">
                    </div>
                </div>
            </section>

            <!-- Recipient Information -->
            <section id="recipientSection" class="form-section hidden">
                <h3 class="text-lg font-semibold mb-4">Recipient Information</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">
                            Recipient Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="recipient_name" name="recipient_name" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">
                            Recipient Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="recipient_email" name="recipient_email" class="form-input">
                    </div>
                </div>
            </section>

            <!-- Request Details -->
            <section class="form-section">
                <h3 class="text-lg font-semibold mb-4">What Are You Requesting?</h3>

                <!-- Reqeuest Type -->
                <div class="mb-6">
                    <label class="form-label">
                        Request Type <span class="text-red-500">*</span>
                    </label>

                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2">
                            <input type="radio" class="form-radio" name="request_type" value="laptop" required>
                            Laptop
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="radio" class="form-radio" name="request_type" value="desktop" required>
                            Desktop Computer
                        </label>

                    </div>
                </div>

                <!-- Budget -->
                <div>
                    <label class="form-label">
                        Computer Budget <span class="text-red-500">*</span>
                    </label>

                    <div class="flex flex-col gap-2">
                        <label class="flex items-center gap-2">
                            <input type="radio" class="form-radio" name="budget_range" value="under_1000" required>
                            Less than $1,000
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="radio" class="form-radio" name="budget_range" value="1000_1499" required>
                            $1,000 to $1,499
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="radio" class="form-radio" name="budget_range" value="1500_1999" required>
                            $1,500 to $1,999
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="radio" class="form-radio" name="budget_range" value="2000_plus" required>
                            Greater than $2,000
                        </label>
                    </div>
                </div>

            </section>

            <!-- Computer Use -->
            <section class="form-section">
                <h3 class="text-lg font-semibold mb-2">
                    How Will You Use the Computer?
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    Select all that apply<span class="text-red-500">*</span>
                </p>

                <div class="space-y-3">
                    <label class="flex items-start gap-2">
                        <input type="checkbox" class="form-checkbox" name="usage[]" value="standard">
                        <span>
                            <strong>Standard usage</strong><br/>
                            <span class="text-sm text-gray-500">
                                Email, web browsing, Microsoft Office, Acrobat, Teams/Zoom
                            </span>
                        </span>
                    </label>

                    <label class="flex items-start gap-2">
                        <input type="checkbox" class="form-checkbox" name="usage[]" value="advanced">
                        <span>
                            <strong>Advanced or specialized usage</strong><br/>
                            <span class="text-sm text-gray-500">
                                AutoCAD, MATLAB, Photoshop, large datasets
                            </span>
                        </span>
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="form-checkbox" id="otherUsageCheckbox" name="usage[]" value="other">
                        Other
                    </label>

                    <!-- Conditional textbox -->
                    <div id="otherUsageContainer" class="hidden">
                        <input
                            type="text"
                            id="otherUsageInput"
                            name="other_usage"
                            class="form-input mt-2"
                            placeholder="Please specify"
                        >
                    </div>

                </div>
            </section>

            <!-- System Preferences -->
            <section class="form-section">
                <h3 class="text-lg font-semibold mb-2">System Preferences</h3>
                <p class="text-sm text-gray-500 mb-4">
                    We will try our best to meet system preferences but cannot guarantee availability.
                </p>

                <!-- Brand Preferences -->
                <div class="mb-6">
                    <label class="form-label">
                        Preferred Brand(s)<span class="text-red-500">*</span>
                    </label>

                    <div class="space-y-2">

                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="brand[]" value="dell" class="form-checkbox brand-option">
                            Dell
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="brand[]" value="lenovo" class="form-checkbox brand-option">
                            Lenovo
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="brand[]" value="apple" class="form-checkbox brand-option">
                            Apple
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="brand[]" value="hp" class="form-checkbox brand-option">
                            HP
                        </label>

                        <!-- Other preference -->
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="brandOtherCheckbox" class="form-checkbox brand-option">
                            Other
                        </label>

                        <div id="brandOtherContainer" class="hidden ml-6">
                            <input
                                type="text"
                                id="brandOtherInput"
                                name="brand_other"
                                class="form-input mt-1"
                                placeholder="Please specify"
                            >
                        </div>

                        <!-- No Preference -->
                        <label class="flex items-center gap-2 mt-2">
                            <input type="checkbox" id="noPreferenceCheckbox" class="form-checkbox">
                            No preference
                        </label>
                    </div>
                </div>

                <!-- Operating System -->
                <div>
                    <label class="form-label">
                        Preferred Operating System <span class="text-red-500">*</span>
                    </label>

                    <div class="space-y-2">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="os" value="windows" class="form-radio" required>
                            Windows
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="radio" name="os" value="macos" class="form-radio" required>
                            macOS
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="radio" name="os" value="linux" class="form-radio" required>
                            Linux
                        </label>

                        <!-- Other -->
                        <label>
                            <input type="radio" id="osOtherRadio" name="os" value="other" class="form-radio" required>
                            Other
                        </label>

                        <div id="osOtherContainer" class="hidden ml-6">
                            <input
                                type="text"
                                id="osOtherInput"
                                name="os_other"
                                class="form-input mt-1"
                                placeholder="Please specify"
                            >
                        </div>
                    </div>
                </div>

            </section>

            <!-- Accessories -->
            <section class="form-section">
                <h3 class="text-lg font-semibold mb-2">Accessories (Optional)</h3>
                <p class="text-sm text-gray-500">
                    Select any accessories you would like to include with your request.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Column 1 -->
                    <div class="space-y-3">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="accessories[]" value="docking_station" class="form-checkbox">
                            Docking station
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="accessories[]" value="wired_keyboard" class="form-checkbox">
                            Wired keyboard
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="accessories[]" value="wireless_keyboard" class="form-checkbox">
                            Wireless keyboard
                        </label>

                        <!-- Other -->
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="accessoryOtherCheckbox" class="form-checkbox">
                            Other
                        </label>
                    </div>

                    <!-- Column 2 -->
                    <div class="space-y-3">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="accessories[]" value="web_camera" class="form-checkbox">
                            Web camera
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="accessories[]" value="wired_mouse" class="form-checkbox">
                            Wired mouse
                        </label>

                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="accessories[]" value="wireless_mouse" class="form-checkbox">
                            Wireless mouse
                        </label>                          
                    </div>
                    
                    <!-- Full-width row -->
                    <div id="accessoryOtherContainer" class="hidden md:col-span-2">
                        <input
                            type="text"
                            id="accessoryOtherInput"
                            name="accessories_other"
                            class="form-input mt-2"
                            placeholder="Please specify"
                        >
                    </div>
                </div>
            </section>
            <!-- Delivery date -->
            <section class="form-section">
                <h3 class="text-lg font-semibold mb-4">Timing & Delivery</h3>

                <label class="form-label">
                    When do you need your computer delivered? <span class="text-red-500">*</span>
                </label>

                <p class="text-sm text-gray-500 mb-3">
                    EngIT requires a minimum of <strong>7 days’</strong> notice for all hardware requests.<br/>
                    EngIT will make every effort to meet your requested date, but delivery timelines cannot be guaranteed.
                </p>

                <input
                    type="date"
                    id="deliveryDate"
                    name="delivery_date"
                    class="form-input"
                    required
                >

            </section>

            <!-- Additional information -->
            <section class="form-section">
                <h3 class="text-lg font-semibold mb-2">Additional Details (Optional)</h3>

                <label class="form-label">
                    Provide any extra information that may help us process your request.
                </label>

                <textarea
                    name="additional_info"
                    rows="4"
                    class="form-input resize-none"
                    placeholder="Please specify"
                    maxlength="250"
                ></textarea>
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