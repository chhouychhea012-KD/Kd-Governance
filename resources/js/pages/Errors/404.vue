<template>
  <v-app>
    <v-container fluid class="fill-height error-bg pa-0">
      <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
      </div>

      <div class="content-layer">
        <div class="text-center z-index-content">
          <div class="glitch-wrapper mb-6">
            <h1 class="glitch-text" data-text="404">404</h1>
          </div>
          
          <v-icon size="100" color="white" class="mb-6 pulse-icon">
            mdi-compass-off-outline
          </v-icon>
          
          <h2 class="text-h3 text-white mb-4 font-weight-bold">Page Not Found</h2>
          
          <p class="text-h6 text-white opacity-80 mb-8 px-4 font-weight-light">
            Oops! The page you're looking for doesn't exist.
          </p>
          
          <div class="d-flex justify-center gap-4 flex-wrap">
            <v-btn 
              color="white" 
              size="large" 
              rounded="lg" 
              class="px-8"
              @click="goHome"
            >
              <v-icon start>mdi-home</v-icon>
              Back to Home
            </v-btn>
            
            <v-btn 
              variant="outlined" 
              color="white" 
              size="large" 
              rounded="lg" 
              class="px-8"
              @click="goBack"
            >
              <v-icon start>mdi-arrow-left</v-icon>
              Go Back
            </v-btn>
          </div>
          
          <p class="text-caption text-white opacity-60 mt-8">
            Error Code: 404 Not Found
          </p>
        </div>
      </div>
    </v-container>
  </v-app>
</template>

<script setup>
const goHome = () => {
  window.location.href = '/dashboard'
}

const goBack = () => {
  window.history.back()
}
</script>

<style scoped>
.error-bg {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  height: 100vh !important;
  position: relative;
  overflow: hidden;
}

/* Floating Geometric Shapes */
.floating-shapes {
  position: absolute;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.shape {
  position: absolute;
  opacity: 0.1;
  animation: float-random 20s ease-in-out infinite;
}

.shape-1 {
  width: 300px;
  height: 300px;
  background: white;
  border-radius: 50%;
  top: 10%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 200px;
  height: 200px;
  background: white;
  border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
  top: 60%;
  right: 15%;
  animation-delay: 2s;
}

.shape-3 {
  width: 150px;
  height: 150px;
  background: white;
  transform: rotate(45deg);
  bottom: 20%;
  left: 20%;
  animation-delay: 4s;
}

.shape-4 {
  width: 250px;
  height: 250px;
  background: white;
  border-radius: 50%;
  top: 30%;
  right: 30%;
  animation-delay: 6s;
}

@keyframes float-random {
  0%, 100% { 
    transform: translate(0, 0) rotate(0deg); 
  }
  25% { 
    transform: translate(50px, -50px) rotate(90deg); 
  }
  50% { 
    transform: translate(-30px, 30px) rotate(180deg); 
  }
  75% { 
    transform: translate(40px, 40px) rotate(270deg); 
  }
}

.content-layer {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 10;
}

.z-index-content {
  position: relative;
  z-index: 20;
}

/* Glitch Effect for 404 */
.glitch-wrapper {
  position: relative;
  display: inline-block;
}

.glitch-text {
  font-size: 15vw;
  color: white;
  font-weight: 900;
  line-height: 1;
  text-shadow: 
    0 0 20px rgba(255,255,255,0.5),
    0 0 40px rgba(255,255,255,0.3);
  position: relative;
  animation: glitch 3s infinite;
}

@keyframes glitch {
  0%, 100% { 
    text-shadow: 
      0 0 20px rgba(255,255,255,0.5),
      2px 2px 0 #ff00de,
      -2px -2px 0 #00ffff;
  }
  25% { 
    text-shadow: 
      0 0 20px rgba(255,255,255,0.5),
      -2px 2px 0 #ff00de,
      2px -2px 0 #00ffff;
  }
  50% { 
    text-shadow: 
      0 0 20px rgba(255,255,255,0.5),
      2px -2px 0 #ff00de,
      -2px 2px 0 #00ffff;
  }
  75% { 
    text-shadow: 
      0 0 20px rgba(255,255,255,0.5),
      -2px -2px 0 #ff00de,
      2px 2px 0 #00ffff;
  }
}

/* Pulse Animation for Icon */
.pulse-icon {
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { 
    opacity: 1;
    transform: scale(1);
  }
  50% { 
    opacity: 0.6;
    transform: scale(1.1);
  }
}

.gap-4 {
  gap: 16px;
}
</style>