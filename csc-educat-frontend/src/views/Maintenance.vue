<template>
    <div class="maintenance-container">
        <img :src="require('@/assets/default/_500.png')" alt="Under Construction" class="maintenance-image">
        <h1 class="maintenance-heading">We will be right back!</h1>
        <p class="maintenance-message">We are working on a few fixes and will be back up shortly. Thank you for your patience.</p>
    </div>
</template>

<style>
    .maintenance-container {
        width: 100%;
        height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    .maintenance-image {
        width: 280px;
    }
    .maintenance-heading {
        font-family: 'Poppins', sans-serif;
        font-weight: bold;
        font-size: 24px;
        color: #34323e;
        margin-top: 20px;
    }
    .maintenance-message {
        color: #848484;
        margin-top: 10px; 
    }
</style>