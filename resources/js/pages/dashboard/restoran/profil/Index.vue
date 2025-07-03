<template>
    <div class="profile-wrapper">
        <!-- Animated background particles -->
        <div class="particles-container">
            <div class="particle" v-for="n in 15" :key="n" :style="getParticleStyle(n)"></div>
        </div>

        <!-- Floating food icons -->
        <div class="floating-icons">
            <div class="floating-icon" v-for="(icon, index) in foodIcons" :key="index"
                 :style="getFloatingIconStyle(index)">
                {{ icon }}
            </div>
        </div>

        <div class="profile-card" :class="{ 'loaded': isLoaded }">
            <!-- Glowing top border with moving gradient -->
            <div class="glowing-border"></div>

            <!-- Header Section with enhanced animations -->
            <div class="profile-header">
                <div class="header-bg-animation"></div>

                <div class="logo-container" @click="triggerLogoAnimation">
                    <div class="logo-glow"></div>
                    <img
                        :src="profil.img"
                        :alt="profil.nama || 'Easy Eats'"
                        class="profile-logo"
                        :class="{ 'logo-spin': logoAnimated }"
                        loading="lazy"
                    >
                    <div class="logo-ring"></div>
                </div>

                <div class="brand-section">
                    <h1 class="brand-title">
                        <span v-for="(char, index) in (profil.nama || 'EASY EATS')"
                              :key="index"
                              class="title-char"
                              :style="{ animationDelay: index * 0.1 + 's' }">
                            {{ char === ' ' ? '\u00A0' : char }}
                        </span>
                    </h1>
                    <p class="brand-description slide-up">{{ profil.deskripsi }}</p>
                    <div class="brand-underline"></div>
                </div>
            </div>

            <!-- Info Section with staggered animations -->
            <div class="info-section">
                <div class="info-cards">
                    <div class="info-card"
                         v-for="(info, index) in infoData"
                         :key="index"
                         :class="{ 'full-width': info.fullWidth }"
                         :style="{ animationDelay: (index * 0.2) + 's' }"
                         @mouseenter="onCardHover(index)"
                         @mouseleave="onCardLeave(index)">

                        <div class="card-glow"></div>
                        <div class="info-header">
                            <div class="info-icon-container">
                                <span class="info-icon">{{ info.icon }}</span>
                                <div class="icon-ripple"></div>
                            </div>
                            <h3 class="info-title">{{ info.title }}</h3>
                        </div>
                        <p class="info-text">{{ info.text }}</p>

                        <!-- Animated corner accent -->
                        <div class="corner-accent"></div>
                    </div>
                </div>
            </div>

            <!-- Interactive pulse effect -->
            <div class="pulse-effect" :class="{ 'active': pulseActive }"></div>
        </div>

        <!-- Success animation overlay -->
        <div class="success-overlay" v-if="showSuccess">
            <div class="success-content">
                <div class="success-icon">✨</div>
                <p>Profil Berhasil Dimuat!</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProfilRestoran',
    data() {
        return {
            profil: {
                nama: 'EASY EATS',
                deskripsi: 'Pesan dan Nikmati Makanan Dengan Mudah - Delivery cepat dengan cita rasa terbaik untuk kepuasan Anda',
                alamat: 'Jl. Citra Husada No. 321, Surabaya, Jawa Timur',
                contact: '031-12345678',
                jam_operasional: 'Buka setiap hari dari jam 10:00 hingga 22:00',
                img: '/media/profil.jpg'
            },
            isLoaded: false,
            logoAnimated: false,
            pulseActive: false,
            showSuccess: false,
            foodIcons: ['🍕', '🍔', '🍟', '🌮', '🍜', '🥗', '🍰', '☕'],
            hoveredCard: -1
        };
    },
    computed: {
        infoData() {
            return [
                {
                    icon: '📍',
                    title: 'Lokasi',
                    text: this.profil.alamat,
                    fullWidth: false
                },
                {
                    icon: '📞',
                    title: 'Kontak',
                    text: this.profil.contact,
                    fullWidth: false
                },
                {
                    icon: '🕒',
                    title: 'Jam Operasional',
                    text: this.profil.jam_operasional,
                    fullWidth: true
                }
            ];
        }
    },
    mounted() {
        // Trigger loading animation
        setTimeout(() => {
            this.isLoaded = true;
            this.showSuccessAnimation();
        }, 500);

        // Auto pulse effect
        setInterval(() => {
            this.pulseActive = true;
            setTimeout(() => {
                this.pulseActive = false;
            }, 1000);
        }, 8000);
    },
    methods: {
        getParticleStyle(index) {
            const size = Math.random() * 6 + 2;
            const left = Math.random() * 100;
            const animationDuration = Math.random() * 20 + 10;
            const animationDelay = Math.random() * 5;

            return {
                left: left + '%',
                width: size + 'px',
                height: size + 'px',
                animationDuration: animationDuration + 's',
                animationDelay: animationDelay + 's'
            };
        },
        getFloatingIconStyle(index) {
            const positions = [
                { top: '10%', left: '5%' },
                { top: '20%', right: '8%' },
                { top: '40%', left: '3%' },
                { top: '60%', right: '5%' },
                { top: '80%', left: '10%' },
                { bottom: '15%', right: '12%' },
                { top: '15%', left: '50%' },
                { bottom: '25%', left: '45%' }
            ];

            const position = positions[index] || { top: '50%', left: '50%' };
            const animationDelay = index * 0.5;

            return {
                ...position,
                animationDelay: animationDelay + 's'
            };
        },
        triggerLogoAnimation() {
            this.logoAnimated = true;
            setTimeout(() => {
                this.logoAnimated = false;
            }, 1000);
        },
        onCardHover(index) {
            this.hoveredCard = index;
        },
        onCardLeave(index) {
            this.hoveredCard = -1;
        },
        showSuccessAnimation() {
            this.showSuccess = true;
            setTimeout(() => {
                this.showSuccess = false;
            }, 2000);
        }
    }
};
</script>

<style scoped>
/* Enhanced CSS Variables */
:root {
    --primary-orange: #FF9500;
    --primary-orange-dark: #E6850E;
    --secondary-orange: #FFB84D;
    --accent-blue: #1E3A8A;
    --bg-light: #FFF7ED;
    --bg-card: #FFFFFF;
    --text-dark: #111827;
    --text-medium: #1F2937;
    --text-light: #374151;
    --shadow-soft: 0 4px 20px rgba(255, 149, 0, 0.1);
    --shadow-medium: 0 8px 30px rgba(0, 0, 0, 0.12);
    --shadow-glow: 0 0 40px rgba(255, 149, 0, 0.4);
    --border-radius: 16px;
    --border-radius-lg: 24px;
    --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    --bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

/* Dark mode variables */
@media (prefers-color-scheme: dark) {
    :root {
        --bg-light: #0F172A;
        --bg-card: #1E293B;
        --text-dark: #F1F5F9;
        --text-medium: #CBD5E1;
        --text-light: #94A3B8;
        --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.3);
        --shadow-medium: 0 8px 30px rgba(0, 0, 0, 0.4);
    }
}

* {
    box-sizing: border-box;
}

/* Enhanced wrapper with overflow for particles */
.profile-wrapper {
    min-height: 100vh;
    background: linear-gradient(135deg, var(--bg-light) 0%, #FEF3E2 50%, #FFE4B5 100%);
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    position: relative;
    overflow: hidden;
}

/* Animated background particles */
.particles-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.particle {
    position: absolute;
    background: linear-gradient(45deg, var(--primary-orange), var(--secondary-orange));
    border-radius: 50%;
    opacity: 0.1;
    animation: floatUp infinite linear;
}

@keyframes floatUp {
    0% {
        transform: translateY(100vh) rotate(0deg);
        opacity: 0;
    }
    10% {
        opacity: 0.1;
    }
    90% {
        opacity: 0.1;
    }
    100% {
        transform: translateY(-100px) rotate(360deg);
        opacity: 0;
    }
}

/* Floating food icons */
.floating-icons {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.floating-icon {
    position: absolute;
    font-size: 1.5rem;
    opacity: 0.3;
    animation: floatIcon 6s ease-in-out infinite;
}

@keyframes floatIcon {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
    }
}

/* Enhanced main card */
.profile-card {
    width: 100%;
    max-width: 900px;
    background: var(--bg-card);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-medium);
    overflow: hidden;
    transition: var(--transition);
    position: relative;
    z-index: 2;
    transform: translateY(20px);
    opacity: 0;
}

.profile-card.loaded {
    transform: translateY(0);
    opacity: 1;
    animation: cardEntrance 0.8s var(--bounce);
}

@keyframes cardEntrance {
    0% {
        transform: translateY(50px) scale(0.9);
        opacity: 0;
    }
    60% {
        transform: translateY(-10px) scale(1.02);
    }
    100% {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

/* Glowing animated border */
.glowing-border {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, var(--primary-orange), var(--secondary-orange), var(--primary-orange));
    background-size: 200% 100%;
    animation: glowingGradient 3s ease-in-out infinite;
}

@keyframes glowingGradient {
    0%, 100% {
        background-position: 0% 50%;
        box-shadow: 0 0 20px rgba(255, 149, 0, 0.5);
    }
    50% {
        background-position: 100% 50%;
        box-shadow: 0 0 30px rgba(255, 149, 0, 0.8);
    }
}

/* Enhanced header with animated background */
.profile-header {
    padding: 2.5rem 2rem;
    text-align: center;
    background: linear-gradient(135deg, #FFF7ED 0%, #FFFFFF 100%);
    position: relative;
    overflow: hidden;
}

.header-bg-animation {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: conic-gradient(from 0deg, transparent, rgba(255, 149, 0, 0.1), transparent);
    animation: rotate 20s linear infinite;
}

@keyframes rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Enhanced logo with multiple effects */
.logo-container {
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 1;
    cursor: pointer;
}

.logo-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 140px;
    height: 140px;
    background: radial-gradient(circle, rgba(255, 149, 0, 0.3), transparent);
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.5;
    }
    50% {
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 0.8;
    }
}

.profile-logo {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--primary-orange);
    box-shadow: var(--shadow-glow);
    transition: var(--transition);
    position: relative;
    z-index: 1;
}

.profile-logo:hover {
    transform: scale(1.1);
    box-shadow: 0 0 50px rgba(255, 149, 0, 0.6);
}

.profile-logo.logo-spin {
    animation: logoSpin 1s var(--bounce);
}

@keyframes logoSpin {
    0% { transform: scale(1) rotate(0deg); }
    50% { transform: scale(1.2) rotate(180deg); }
    100% { transform: scale(1) rotate(360deg); }
}

.logo-ring {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 140px;
    height: 140px;
    border: 2px solid rgba(255, 149, 0, 0.3);
    border-radius: 50%;
    border-top-color: var(--primary-orange);
    animation: spin 3s linear infinite;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Animated title characters */
.brand-title {
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    font-weight: 800;
    color: black;
    margin: 0 0 0.5rem 0;
    letter-spacing: 2px;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.2);
    filter: drop-shadow(0 2px 4px rgba(255, 149, 0, 0.3));
    position: relative;
}

.title-char {
    display: inline-block;
    animation: bounceIn 0.8s var(--bounce) both;
}

@keyframes bounceIn {
    0% {
        transform: translateY(-30px) scale(0);
        opacity: 0;
    }
    50% {
        transform: translateY(0) scale(1.1);
    }
    100% {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

.brand-description {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    color: black;
    line-height: 1.6;
    margin: 0 0 1rem 0;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    font-weight: 500;
}

.slide-up {
    animation: slideUp 1s ease-out 0.5s both;
}

@keyframes slideUp {
    0% {
        transform: translateY(20px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

.brand-underline {
    height: 3px;
    width: 0;
    background: linear-gradient(90deg, var(--primary-orange), var(--secondary-orange));
    margin: 0 auto;
    border-radius: 2px;
    animation: expandLine 1s ease-out 1s both;
}

@keyframes expandLine {
    0% { width: 0; }
    100% { width: 100px; }
}

/* Enhanced info section */
.info-section {
    padding: 2rem;
    background: var(--bg-card);
}

.info-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    max-width: 800px;
    margin: 0 auto;
}

.info-card {
    background: #FFFFFF;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    border: 2px solid rgba(255, 149, 0, 0.15);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    transform: translateY(20px);
    opacity: 0;
    animation: cardSlideIn 0.6s ease-out both;
}

@keyframes cardSlideIn {
    0% {
        transform: translateY(30px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

.card-glow {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 149, 0, 0.1), transparent);
    transform: scale(0);
    transition: var(--transition);
}

.info-card:hover .card-glow {
    transform: scale(1);
}

.info-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: var(--shadow-glow);
    border-color: var(--primary-orange);
}

.info-card.full-width {
    grid-column: 1 / -1;
}

/* Enhanced info header */
.info-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.info-icon-container {
    position: relative;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(255, 149, 0, 0.3);
    transition: var(--transition);
}

.info-icon-container:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(255, 149, 0, 0.5);
}

.info-icon {
    font-size: 1.2rem;
    position: relative;
    z-index: 1;
}

.icon-ripple {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform-origin: center;
    animation: ripple 2s ease-out infinite;
}

@keyframes ripple {
    0% {
        transform: translate(-50%, -50%) scale(0.8);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.4);
        opacity: 0;
    }
}

.info-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: black;
    margin: 0;
}

.info-text {
    color: black;
    line-height: 1.5;
    margin: 0;
    font-size: 1rem;
    font-weight: 500;
}

/* Corner accent animation */
.corner-accent {
    position: absolute;
    top: 0;
    right: 0;
    width: 0;
    height: 0;
    border-left: 20px solid transparent;
    border-top: 20px solid var(--primary-orange);
    opacity: 0;
    transition: var(--transition);
}

.info-card:hover .corner-accent {
    opacity: 1;
    animation: cornerGlow 1s ease-in-out;
}

@keyframes cornerGlow {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

/* Pulse effect */
.pulse-effect {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 200px;
    height: 200px;
    border: 2px solid rgba(255, 149, 0, 0.5);
    border-radius: 50%;
    opacity: 0;
    pointer-events: none;
}

.pulse-effect.active {
    animation: pulseRing 1s ease-out;
}

@keyframes pulseRing {
    0% {
        transform: translate(-50%, -50%) scale(0.1);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(4);
        opacity: 0;
    }
}

/* Success overlay */
.success-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease-out;
}

.success-content {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    animation: successBounce 0.6s var(--bounce);
}

.success-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    animation: sparkle 1s ease-in-out infinite;
}

@keyframes sparkle {
    0%, 100% { transform: scale(1) rotate(0deg); }
    50% { transform: scale(1.2) rotate(180deg); }
}

@keyframes successBounce {
    0% {
        transform: scale(0.3);
        opacity: 0;
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes fadeIn {
    0% { opacity: 0; }
    100% { opacity: 1; }
}

/* Enhanced responsive design */
@media (max-width: 768px) {
    .profile-wrapper {
        padding: 0.5rem;
    }

    .profile-header {
        padding: 2rem 1.5rem;
    }

    .info-section {
        padding: 1.5rem;
    }

    .info-cards {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .profile-logo {
        width: 100px;
        height: 100px;
    }

    .logo-glow {
        width: 120px;
        height: 120px;
    }

    .floating-icon {
        font-size: 1.2rem;
    }
}

@media (max-width: 480px) {
    .profile-wrapper {
        padding: 0.25rem;
        align-items: flex-start;
        padding-top: 1rem;
    }

    .profile-header {
        padding: 1.5rem 1rem;
    }

    .info-section {
        padding: 1rem;
    }

    .info-card {
        padding: 1rem;
    }

    .profile-logo {
        width: 80px;
        height: 80px;
    }

    .logo-glow {
        width: 100px;
        height: 100px;
    }

    .floating-icon {
        font-size: 1rem;
    }
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }

    .particle,
    .floating-icon,
    .header-bg-animation,
    .logo-ring,
    .icon-ripple {
        animation: none !important;
    }
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .info-card {
        border-width: 3px;
        border-color: var(--text-dark);
    }

    .brand-title {
        text-shadow: none;
    }

    .glowing-border {
        background: var(--primary-orange);
    }
}

/* Print styles */
@media print {
    .profile-wrapper {
        background: white;
        min-height: auto;
    }

    .profile-card {
        box-shadow: none;
        border: 1px solid #ccc;
    }

    .particles-container,
    .floating-icons,
    .glowing-border,
    .header-bg-animation,
    .logo-glow,
    .logo-ring,
    .card-glow,
    .icon-ripple,
    .corner-accent,
    .pulse-effect {
        display: none;
    }
}
</style>
