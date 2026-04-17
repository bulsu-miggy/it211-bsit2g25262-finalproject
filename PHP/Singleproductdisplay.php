<?php
// Halo-Halo Product Page - Lasa Filipina
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halo-Halo | Lasa Filipina</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .bg-gradient-custom {
            background: linear-gradient(to bottom, #fed7aa, #ffffff);
        }
        .text-shadow {
            text-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body class="bg-white overflow-x-hidden">

<div class="relative w-full max-w-[1280px] mx-auto bg-white min-h-[916px] overflow-hidden">

    <!-- Background decorative blocks -->
    <div class="absolute w-[1331px] h-96 -left-[38px] -top-[2px] bg-orange-200"></div>
    <div class="absolute w-[1349px] h-72 -left-[18px] top-[339px] bg-gradient-to-b from-orange-200 to-white"></div>
    <div class="absolute w-[1331px] h-96 -left-[18px] top-[791px] bg-orange-200"></div>
    <div class="absolute w-[1331px] h-28 -left-[75px] top-[709px] bg-gradient-to-l from-orange-200 via-orange-50 to-white"></div>

    <!-- Social / Decorative Icons bottom right -->
    <div class="absolute left-[1119px] top-[778px] inline-flex gap-6">
        <div class="w-6 h-6 bg-black/40 rounded-sm"></div>
        <div class="w-6 h-6 bg-white rounded-sm"></div>
        <div class="w-6 h-6 bg-black/40 rounded-sm"></div>
    </div>

    <!-- Navigation Bar -->
    <div class="absolute top-[13px] left-[42px] text-black text-xl font-bold leading-6">Lasa Filipina</div>
    <div class="absolute top-[26px] left-[358px] text-black text-base font-normal leading-5 underline">Home</div>
    <div class="absolute top-[26px] left-[502px] text-white text-base font-normal leading-5">
        <span class="inline-block bg-yellow-950 rounded-3xl px-6 py-1 -mt-1">Menu</span>
    </div>
    <div class="absolute top-[26px] left-[644px] text-black text-base font-normal leading-5">About Us</div>
    <div class="absolute top-[26px] left-[821px] text-black text-base font-normal leading-5 tracking-wide">Contact Us</div>

    <!-- Avatar / Profile Circle -->
    <div class="absolute w-10 h-9 left-[1201px] top-[17px] bg-zinc-500 rounded-full"></div>
    <div class="absolute w-2.5 h-2.5 left-[1215px] top-[25px] bg-white rounded-full"></div>
    <div class="absolute w-6 h-2.5 left-[1210px] top-[36px] bg-white rounded-full"></div>

    <!-- Decorative lines near avatar (simplified) -->
    <div class="absolute w-1.5 h-1 left-[1125px] top-[43px] bg-black rounded-full"></div>
    <div class="absolute w-7 h-px left-[1099px] top-[18px] rotate-[72.9deg] bg-black origin-top-left"></div>
    <div class="absolute w-8 h-px left-[1102px] top-[28px] rotate-[-1.79deg] bg-black origin-top-left"></div>
    <div class="absolute w-5 h-px left-[1108px] top-[44px] bg-black"></div>
    <div class="absolute w-4 h-px left-[1133px] top-[27px] rotate-[103.24deg] bg-black origin-top-left"></div>
    <div class="absolute w-5 h-px left-[1108px] top-[27px] rotate-[70.56deg] bg-black origin-top-left"></div>
    <div class="absolute w-4 h-px left-[1126px] top-[28px] rotate-[104.04deg] bg-black origin-top-left"></div>
    <div class="absolute w-4 h-px left-[1118px] top-[28px] rotate-[86.42deg] bg-black origin-top-left"></div>
    <div class="absolute w-1.5 h-1 left-[1096px] top-[17px] bg-black rounded-full"></div>
    <div class="absolute w-1.5 h-1 left-[1105px] top-[43px] bg-black rounded-full"></div>
    <div class="absolute w-7 h-px left-[1104px] top-[31px] bg-black"></div>
    <div class="absolute w-7 h-px left-[1104px] top-[35px] bg-black"></div>
    <div class="absolute w-6 h-px left-[1106px] top-[39px] rotate-[-2.29deg] bg-black origin-top-left"></div>
    <div class="absolute w-5 h-px left-[1107px] top-[41px] bg-black"></div>

    <!-- Main Card Container (Left: Image, Right: Text + Buttons) -->
    <div class="absolute left-[40px] top-[86px] w-[626px] h-[670px] bg-white rounded-[20px] border border-black"></div>
    <div class="absolute left-[697px] top-[86px] w-[545px] h-[463px] bg-white rounded-[20px] border border-black"></div>

    <!-- Product Image -->
    <img class="absolute w-[544px] h-96 left-[82px] top-[115px] rounded-[20px] object-cover" src="https://placehold.co/544x420?text=Halo-Halo+Dish" alt="Halo-Halo Dessert" />

    <!-- Product Title -->
    <div class="absolute left-[53px] top-[549px] w-[647px] h-24 text-black text-8xl font-black leading-[93px] text-shadow">HALO - HALO</div>

    <!-- Price -->
    <div class="absolute left-[425px] top-[676px] w-[647px] h-20 text-black text-6xl font-black leading-[64px]">49 PHP</div>

    <!-- Product Description -->
    <div class="absolute left-[715px] top-[188px] w-[518px] h-64 text-center text-black text-2xl font-bold leading-7">
        Cool down with a refreshing taste of the Philippines with Lasa Filipina’s signature Halo-Halo! 
        This vibrant dessert is a delightful mix of crushed ice, sweetened fruits, creamy leche flan, 
        soft ube, and chewy jellies, all layered together and topped with rich evaporated milk. 
        Every spoonful offers a perfect balance of sweetness, creaminess, and texture that melts in your mouth.<br/><br/>
        Perfectly portioned for one, this indulgent treat is your go-to for beating the heat and satisfying 
        your sweet cravings—whether after a meal or as a midday pick-me-up.<br/><br/>
        A true Filipino classic, made with love in every layer.
    </div>

    <!-- Action Buttons -->
    <div class="absolute left-[709px] top-[572px] w-[533px] h-20 opacity-90 bg-yellow-950 rounded-[20px] border border-yellow-950 flex items-center justify-center">
        <span class="text-white text-4xl font-bold leading-[48px]">ADD TO CART</span>
    </div>
    <div class="absolute left-[709px] top-[669px] w-[533px] h-20 opacity-90 bg-yellow-950 rounded-[20px] border border-yellow-950 flex items-center justify-center">
        <span class="text-white text-4xl font-bold leading-[48px]">BACK TO MENU</span>
    </div>

    <!-- Footer -->
    <div class="absolute left-[43px] top-[764px] text-black text-xl font-bold leading-6">Lasa Filipina</div>
    <div class="absolute left-[867px] top-[778px] text-black text-base font-normal leading-5">https://www.LasaFilipina.com</div>

</div>

<!-- Bottom yellow bar -->
<div class="w-full max-w-[1198px] mx-auto mt-4 h-24 bg-yellow-950 rounded-3xl mb-8"></div>

</body>
</html>