<template>
  <v-app>
    <v-container fluid class="fill-height premium-bg pa-0">
      <div class="plexus-network"></div>
      
      <div class="glow-spot spot-1"></div>
      <div class="glow-spot spot-2"></div>

      <div class="glass-layer">
        <div class="text-center z-index-content">
          <h1 class="cyber-403">403</h1>
          
          <div class="divider-wrap mb-8">
            <div class="animated-line"></div>
          </div>
          
          <h2 class="text-h4 text-white mb-4 font-weight-light">Access Denied</h2>
          
          <p class="text-body-1 text-white opacity-80 mb-8 px-4">
            You do not have permission to access this page.
          </p>
          
          <v-btn 
            variant="outlined" 
            color="white" 
            rounded="pill" 
            size="x-large" 
            class="action-btn px-12"
            @click="goBack"
          >
            <v-icon start>mdi-arrow-left</v-icon>
            GO BACK HOME
          </v-btn>
          
          <p class="text-caption text-white opacity-60 mt-6 letter-spacing-wide">
            Error Code: 403 Forbidden
          </p>
        </div>
      </div>
    </v-container>
  </v-app>
</template>

<script setup>
const goBack = () => {
  window.location.href = '/dashboard'
}
</script>

<style scoped>
/* Full Screen Animated Gradient */
.premium-bg {
  background: linear-gradient(-45deg, #0f0c29, #302b63, #24243e, #000000);
  background-size: 400% 400%;
  animation: gradientAnimation 15s ease infinite;
  height: 100vh !important;
  position: relative;
  overflow: hidden;
}

@keyframes gradientAnimation {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

/* Simulated Network/Plexus Lines */
.plexus-network {
  position: absolute;
  width: 100%;
  height: 100%;
  background-image: 
    linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
  background-size: 100px 100px;
  mask-image: radial-gradient(circle, black, transparent 80%);
  transform: perspective(500px) rotateX(60deg) translateY(-100px);
  animation: moveGrid 20s linear infinite;
}

@keyframes moveGrid {
  from { background-position: 0 0; }
  to { background-position: 0 1000px; }
}

/* Floating Light Blobs */
.glow-spot {
  position: absolute;
  width: 40vw;
  height: 40vw;
  border-radius: 50%;
  filter: blur(100px);
  z-index: 1;
  opacity: 0.3;
}
.spot-1 {
  background: #302b63;
  top: -10%;
  left: -10%;
  animation: float 10s ease-in-out infinite;
}
.spot-2 {
  background: #24243e;
  bottom: -10%;
  right: -10%;
  animation: float 12s ease-in-out infinite reverse;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0); }
  50% { transform: translate(30px, 50px); }
}

.glass-layer {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
  z-index: 2;
}

.z-index-content {
  position: relative;
  z-index: 10;
}

.cyber-403 {
  font-size: 18vw;
  color: white;
  font-weight: 100;
  line-height: 1;
  text-shadow: 0 0 30px rgba(255,255,255,0.2);
  letter-spacing: -10px;
}

/* Animated Divider Line */
.divider-wrap {
  width: 150px;
  height: 2px;
  background: rgba(255,255,255,0.1);
  margin: 0 auto;
  overflow: hidden;
}

.animated-line {
  width: 100%;
  height: 100%;
  background: white;
  animation: lineSlide 2s ease-in-out infinite;
}

@keyframes lineSlide {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.letter-spacing-wide {
  letter-spacing: 5px !important;
}

.action-btn {
  border-color: rgba(255,255,255,0.5) !important;
  transition: all 0.3s ease;
}

.action-btn:hover {
  background: white !important;
  color: black !important;
  box-shadow: 0 0 20px rgba(255,255,255,0.4);
}
</style>