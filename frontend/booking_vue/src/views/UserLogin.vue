<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const email = ref('');
const password = ref('');
const validationErrors = ref({});
const credentialError = ref('');
const router = useRouter();
const auth = useAuthStore();

const submit = async () => {
  validationErrors.value = {};
  credentialError.value = '';

  try {
    await auth.login({
      email: email.value.trim(),
      password: password.value.trim(),
    });

    await auth.fetchUser(); // Fetch user info after login to populate user state

    router.push(auth.redirect_to || '/user/dashboard');
  } catch (e) {
    if (e.response && e.response.status === 422 && e.response.data.errors) {
      validationErrors.value = e.response.data.errors;
    } else if (e.response && e.response.data && e.response.data.error) {
      credentialError.value = e.response.data.error;
    } else if (e.response && e.response.data && e.response.data.message) {
      credentialError.value = e.response.data.message;
    } else {
      credentialError.value = 'Something went wrong.';
    }
  }
};
</script>


<template>
  <div class="login-container">
    <div class="login-card">
      <h2 class="login-title">Login</h2>
      <form @submit.prevent="submit" class="login-form">
        <input
          v-model="email"
          placeholder="Email"
          type="email"
          class="login-input"
          autocomplete="username"
        />
        <span v-if="validationErrors.email" class="input-error">
          {{ validationErrors.email[0] }}
        </span>

        <input
          v-model="password"
          placeholder="Password"
          type="password"
          class="login-input"
          autocomplete="current-password"
        />
        <span v-if="validationErrors.password" class="input-error">
          {{ validationErrors.password[0] }}
        </span>

        <button type="submit" class="login-button">Login</button>
        <br>
        <router-link to="/forgot-password">Forgot Password?</router-link>

      </form>

      <p v-if="credentialError" class="error-text">{{ credentialError }}</p>
    </div>
  </div>
</template>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: #f5f6fa;
}

.login-card {
  background: #fff;
  padding: 2rem 2.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

.login-title {
  text-align: center;
  margin-bottom: 1.5rem;
  font-size: 1.8rem;
  color: #333;
}

.login-form {
  display: flex;
  flex-direction: column;
}

.login-input {
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 1rem;
  outline: none;
  transition: border 0.2s;
}

.login-input:focus {
  border-color: #007bff;
}

.login-button {
  background-color: #007bff;
  color: white;
  padding: 0.75rem;
  font-size: 1rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.login-button:hover {
  background-color: #0056b3;
}

.error-text {
  margin-top: 1rem;
  color: #e74c3c;
  font-weight: 500;
  text-align: center;
}

.input-error {
  color: #e74c3c;
  font-size: 0.9rem;
  margin-top: -0.75rem;
  margin-bottom: 0.75rem;
  padding-left: 0.25rem;
}
</style>
