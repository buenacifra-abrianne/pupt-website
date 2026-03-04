<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polytechnic University of the Philippines - Taguig Campus</title>
    <link rel="stylesheet" href="{{ asset('assets/css/loginstyle.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <meta name="theme-color" content="#8B0000">
</head>
<body>

<main class="menu">

    <!-- INNER CONTAINER -->
    <div class="landing-container">
    

    <!-- Logos -->
    <div class="big_logo">
        <img
            src="{{ asset('assets/static_img/logo.png') }}"
            alt="PUPT Logo"
        >
        <img
            src="{{ asset('assets/static_img/bagong_pilipinas_logo.png') }}"
            alt="Bagong Pilipinas Logo"
        >
    </div>

    <div class="small_logo">
        <img
            src="{{ asset('assets/static_img/transparency_seal.png') }}"
            alt="Transparency Seal"
        >
        <img
            src="{{ asset('assets/static_img/freedom_of_information.png') }}"
            alt="Freedom of Information"
        >
        <img
            src="{{ asset('assets/static_img/DPO_DPS_seal.png') }}"
            alt="DPO DPS Seal"
        >
    </div>


        <!-- Title -->
        <h2 class="Title">Welcome to the
            Polytechnic University of the Philippines <br> Taguig Campus</h2>

        <!-- Subtitle -->
        <p class="destination-hint">
            <svg xmlns="http://www.w3.org/2000/svg"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke-width="1.5"
                 stroke="currentColor"
                 class="arrow-inline">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
            Please click/tap the appropriate link to help you in your navigation of our services
        </p>

        <!-- Buttons -->
        <div class="button-container">

            <a href="{{ route('faculty.login') }}" class="portal-button">
                <span class="icon">
                    <!-- users -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
</svg>

                </span>
                <span class="text">Faculty</span>
            </a>

            <a href="#" class="portal-button">
                <span class="icon">
                    <!-- academic-cap -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
</svg>

                </span>
                <span class="text">Student</span>
            </a>

            <a href="#" class="portal-button">
                <span class="icon">
                    <!-- beaker -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
</svg>

                </span>
                <span class="text">Research</span>
            </a>

            <a href="#" class="portal-button">
                <span class="icon">
                    <!-- wrench -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0 0 15 0m-15 0a7.5 7.5 0 1 1 15 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077 1.41-.513m14.095-5.13 1.41-.513M5.106 17.785l1.15-.964m11.49-9.642 1.149-.964M7.501 19.795l.75-1.3m7.5-12.99.75-1.3m-6.063 16.658.26-1.477m2.605-14.772.26-1.477m0 17.726-.26-1.477M10.698 4.614l-.26-1.477M16.5 19.794l-.75-1.299M7.5 4.205 12 12m6.894 5.785-1.149-.964M6.256 7.178l-1.15-.964m15.352 8.864-1.41-.513M4.954 9.435l-1.41-.514M12.002 12l-3.75 6.495" />
</svg>

                </span>
                <span class="text">Services</span>
            </a>

            <a href="{{ route('public.home') }}" class="portal-button">
                <span class="icon">
                    <!-- home -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
</svg>

                </span>
                <span class="text">View Homepage</span>
            </a>

        </div>
    </div>

</main>

</body>
</html>