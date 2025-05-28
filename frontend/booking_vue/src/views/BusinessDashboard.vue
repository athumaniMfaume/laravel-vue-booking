<template>
  <div class="dashboard-container">
    <div class="header">
      <h2>Business Dashboard</h2>
      <button class="logout-btn" @click="logout">Logout</button>
    </div>

    <p class="welcome-msg">Welcome, {{ auth.user?.name || 'business user' }}!</p>

    <div class="links">
      <router-link to="/business/service" class="nav-link">🛠️ Manage Services</router-link>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const auth = useAuthStore();

auth.fetchUser();

const logout = () => {
  auth.logout();
  router.push('/login');
};
</script>

<style scoped>
.dashboard-container {
  max-width: 600px;
  margin: 40px auto;
  padding: 20px;
  background: #fdfdfd;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  font-family: Arial, sans-serif;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.logout-btn {
  background-color: #e74c3c;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.logout-btn:hover {
  background-color: #c0392b;
}

.welcome-msg {
  font-size: 1rem;
  margin-bottom: 20px;
}

.links {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.nav-link {
  padding: 12px;
  background: #27ae60;
  color: white;
  text-align: center;
  border-radius: 8px;
  text-decoration: none;
  transition: background 0.3s ease;
}

.nav-link:hover {
  background: #1e8449;
}
</style>
