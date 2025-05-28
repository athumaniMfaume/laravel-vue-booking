<template>
  <div class="admin-users">
    <h1 class="page-title">Manage Users</h1>

    <!-- Success Message -->
    <div v-if="message" class="alert-success">
      {{ message }}
    </div>

    <!-- Show User Details -->
    <div v-if="selectedUser" class="card">
      <h3>User Details (ID: {{ selectedUser.id }})</h3>
      <p><strong>Name:</strong> {{ selectedUser.name }}</p>
      <p><strong>Email:</strong> {{ selectedUser.email }}</p>
      <p><strong>Role:</strong> {{ selectedUser.role }}</p>
      <button class="btn-sm btn-secondary" @click="selectedUser = null">Close</button>
    </div>

    <!-- User Form -->
    <div class="card">
      <form @submit.prevent="handleSubmit" class="user-form">
        <div class="form-group">
          <label>Name</label>
          <input v-model="form.name" placeholder="Enter name" />
          <span class="error" v-if="errors.name">{{ errors.name[0] }}</span>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" placeholder="Enter email" type="email" />
          <span class="error" v-if="errors.email">{{ errors.email[0] }}</span>
        </div>

        <div class="form-group">
          <label>Password</label>
          <input v-model="form.password" placeholder="Password" type="password" />
          <span class="error" v-if="errors.password">{{ errors.password[0] }}</span>
        </div>

        <div class="form-group">
          <label>Confirm Password</label>
          <input
            v-model="form.password_confirmation"
            placeholder="Confirm password"
            type="password"
          />
          <span class="error" v-if="errors.password_confirmation">
            {{ errors.password_confirmation[0] }}
          </span>
        </div>

        <div class="form-group">
          <label>Role</label>
          <select v-model="form.role">
            <option value="user">User</option>
            <option value="business">Business</option>
            <option value="admin">Admin</option>
          </select>
          <span class="error" v-if="errors.role">{{ errors.role[0] }}</span>
        </div>

        <button type="submit" class="btn-primary">
          {{ editingId ? 'Update' : 'Add' }} User
        </button>
      </form>
    </div>

    <!-- Users Table -->
    <div class="table-container">
      <h2 class="table-title">Users List</h2>
      <table class="styled-table">
        <thead>
          <tr>
            <th>#ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.id }}</td>
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.role }}</td>
            <td>
              <button class="btn-sm btn-secondary" @click="editUser(user)">Edit</button>
              <button class="btn-sm btn-info" @click="showUser(user.id)">Show</button>
              <button class="btn-sm btn-danger" @click="deleteUser(user.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, watch } from 'vue'
import api from '@/utils/axios'

const users = ref([])
const editingId = ref(null)
const message = ref('')
const errors = ref({})
const selectedUser = ref(null)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'user',
})

const fetchUsers = async () => {
  try {
    const res = await api.get('/admin/users')
    users.value = res.data.users
  } catch (err) {
    console.error('Failed to fetch users.', err)
  }
}

const handleSubmit = async () => {
  errors.value = {}
  try {
    let res
    if (editingId.value) {
      res = await api.put(`/admin/users/${editingId.value}`, form)
    } else {
      res = await api.post('/admin/users', form)
    }
    message.value = res.data.message || 'User saved successfully.'
    resetForm()
    fetchUsers()
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors
    } else {
      message.value = 'An error occurred.'
    }
  }
}

const deleteUser = async (id) => {
  if (!confirm('Are you sure you want to delete this user?')) return
  try {
    const res = await api.delete(`/admin/users/${id}`)
    message.value = res.data.message || 'User deleted successfully.'
    fetchUsers()
  } catch (err) {
    message.value = 'Failed to delete user.'
  }
}

const editUser = (user) => {
  editingId.value = user.id
  form.name = user.name
  form.email = user.email
  form.password = ''
  form.password_confirmation = ''
  form.role = user.role
  errors.value = {}
}

const showUser = async (id) => {
  try {
    const res = await api.get(`/admin/users/${id}`)
    selectedUser.value = res.data.user
  } catch (err) {
    message.value = 'Failed to fetch user details.'
    selectedUser.value = null
  }
}

const resetForm = () => {
  editingId.value = null
  form.name = ''
  form.email = ''
  form.password = ''
  form.password_confirmation = ''
  form.role = 'user'
  errors.value = {}
}

watch(message, (val) => {
  if (val) {
    setTimeout(() => {
      message.value = ''
    }, 3000)
  }
})

onMounted(fetchUsers)
</script>

<style scoped>
/* You already provided styles, keeping them same */
.admin-users {
  max-width: 900px;
  margin: auto;
  padding: 2rem;
  font-family: 'Segoe UI', sans-serif;
  color: #333;
}

.page-title {
  font-size: 1.8rem;
  font-weight: bold;
  margin-bottom: 1.5rem;
}

.alert-success {
  background-color: #e6ffed;
  color: #1a7f37;
  padding: 0.75rem 1rem;
  border-left: 4px solid #1a7f37;
  margin-bottom: 1rem;
  border-radius: 5px;
}

.card {
  background-color: #f9f9f9;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 0 10px #eee;
  margin-bottom: 2rem;
}

.user-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: 500;
  margin-bottom: 0.3rem;
}

input,
select {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1rem;
}

.error {
  color: #e53935;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.btn-primary {
  background-color: #1976d2;
  color: #fff;
  padding: 0.6rem 1rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease;
}

.btn-primary:hover {
  background-color: #1565c0;
}

.table-container {
  margin-top: 2rem;
}

.table-title {
  font-size: 1.4rem;
  margin-bottom: 1rem;
  font-weight: 600;
}

.styled-table {
  width: 100%;
  border-collapse: collapse;
  background-color: #fff;
  box-shadow: 0 0 10px #f0f0f0;
  border-radius: 6px;
  overflow: hidden;
}

.styled-table th,
.styled-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #e0e0e0;
}

.styled-table th {
  background-color: #f1f1f1;
  font-weight: bold;
}

.btn-sm {
  padding: 0.35rem 0.75rem;
  font-size: 0.875rem;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  margin-right: 0.5rem;
}

.btn-secondary {
  background-color: #fbbc04;
  color: #fff;
}

.btn-secondary:hover {
  background-color: #e6a700;
}

.btn-info {
  background-color: #2196f3;
  color: #fff;
}

.btn-info:hover {
  background-color: #1976d2;
}

.btn-danger {
  background-color: #e53935;
  color: #fff;
}

.btn-danger:hover {
  background-color: #c62828;
}
</style>
