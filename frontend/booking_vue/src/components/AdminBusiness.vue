<template>
  <div class="admin-business">
    <h2>Businesses</h2>

    <div v-if="message" class="message">{{ message }}</div>

    <!-- Add/Edit Form -->
    <form @submit.prevent="editing ? updateBusiness() : addBusiness()" class="business-form">
      <div class="form-group">
        <label for="name">Name:</label>
        <input id="name" v-model="form.name" type="text" />
        <div v-if="errors.name" class="error-message">
          <span v-for="(error, index) in errors.name" :key="index">{{ error }}</span>
        </div>
      </div>

      <div class="form-group">
        <label for="user">User:</label>
        <select id="user" v-model="form.user_id">
          <option disabled value="">Please select a user</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }} ({{ user.role }})
          </option>
        </select>
        <div v-if="errors.user_id" class="error-message">
          <span v-for="(error, index) in errors.user_id" :key="index">{{ error }}</span>
        </div>
      </div>

      <div class="form-group">
        <label for="opening_hours">Opening Hours:</label>
        <input id="opening_hours" v-model="form.opening_hours" type="text" />
        <div v-if="errors.opening_hours" class="error-message">
          <span v-for="(error, index) in errors.opening_hours" :key="index">{{ error }}</span>
        </div>
      </div>

      <div class="form-group">
        <label for="status">Status:</label>
        <select id="status" v-model="form.status">
          <option value="open">Open</option>
          <option value="closed">Closed</option>
        </select>
        <div v-if="errors.status" class="error-message">
          <span v-for="(error, index) in errors.status" :key="index">{{ error }}</span>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary">
          {{ editing ? 'Update' : 'Add' }} Business
        </button>
        <button type="button" @click="resetForm" v-if="editing" class="btn-secondary">Cancel</button>
      </div>
    </form>

    <hr />

    <!-- Business Details View -->
    <div v-if="selectedBusiness" class="business-details">
      <h3>Business Details</h3>
      <p><strong>Name:</strong> {{ selectedBusiness.name }}</p>
      <p><strong>User:</strong> {{ selectedBusiness.user?.name }}</p>
      <p><strong>User Role:</strong> {{ selectedBusiness.user?.role }}</p>
      <p><strong>Opening Hours:</strong> {{ selectedBusiness.opening_hours }}</p>
      <p><strong>Status:</strong> {{ selectedBusiness.status }}</p>
      <button class="btn-secondary" @click="selectedBusiness = null">Close</button>
    </div>

    <!-- Businesses Table -->
    <table class="business-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>User</th>
          <th>Role</th>
          <th>Opening Hours</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="business in businesses" :key="business.id">
          <td>{{ business.name }}</td>
          <td>{{ getUserName(business.user_id) }}</td>
          <td>{{ getUserRole(business.user_id) }}</td>
          <td>{{ business.opening_hours }}</td>
          <td>{{ business.status }}</td>
          <td>
            <button @click="editBusiness(business)" class="btn-small">Edit</button>
            <button @click="getBusinessDetails(business.id)" class="btn-info">View</button>
            <button @click="deleteBusiness(business.id)" class="btn-danger">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import api from '@/utils/axios';

export default {
  data() {
    return {
      businesses: [],
      users: [],
      form: {
        id: null,
        name: '',
        user_id: '',
        opening_hours: '',
        status: 'open',
      },
      editing: false,
      message: '',
      errors: {},
      selectedBusiness: null, // New: to store selected business details
    };
  },
  created() {
    this.fetchBusinesses();
    this.fetchUsers();
  },
  methods: {
    fetchBusinesses() {
      api.get('/admin/businesses')
        .then((response) => {
          this.businesses = response.data.businesses || [];
        })
        .catch(() => {
          this.message = 'Failed to load businesses.';
          this.clearMessage();
        });
    },
    fetchUsers() {
      api.get('/admin/users')
        .then((response) => {
          this.users = response.data.users || [];
        })
        .catch(() => {
          this.message = 'Failed to load users.';
          this.clearMessage();
        });
    },
    getUserName(userId) {
      const user = this.users.find((u) => u.id === userId);
      return user ? user.name : 'Unknown';
    },
    getUserRole(userId) {
      const user = this.users.find((u) => u.id === userId);
      return user ? user.role : 'Unknown';
    },
    addBusiness() {
      this.errors = {};
      api.post('/admin/businesses', this.form)
        .then((response) => {
          this.message = response.data.message;
          this.fetchBusinesses();
          this.resetForm();
          this.clearMessage();
        })
        .catch((error) => {
          if (error.response?.status === 422) {
            this.errors = error.response.data.errors;
          } else {
            this.message = 'Failed to add business.';
            this.clearMessage();
          }
        });
    },
    updateBusiness() {
      this.errors = {};
      api.put(`/admin/businesses/${this.form.id}`, this.form)
        .then((response) => {
          this.message = response.data.message;
          this.fetchBusinesses();
          this.resetForm();
          this.clearMessage();
        })
        .catch((error) => {
          if (error.response?.status === 422) {
            this.errors = error.response.data.errors;
          } else {
            this.message = 'Failed to update business.';
            this.clearMessage();
          }
        });
    },
    deleteBusiness(id) {
      if (!confirm('Are you sure you want to delete this business?')) return;
      api.delete(`/admin/businesses/${id}`)
        .then((response) => {
          this.message = response.data.message;
          this.fetchBusinesses();
          this.clearMessage();
        })
        .catch(() => {
          this.message = 'Failed to delete business.';
          this.clearMessage();
        });
    },
    editBusiness(business) {
      this.editing = true;
      this.form = { ...business };
    },
    resetForm() {
      this.editing = false;
      this.form = {
        id: null,
        name: '',
        user_id: '',
        opening_hours: '',
        status: 'open',
      };
      this.errors = {};
    },
    clearMessage() {
      setTimeout(() => {
        this.message = '';
      }, 3000);
    },
    getBusinessDetails(id) {
      api.get(`/admin/businesses/${id}`)
        .then((response) => {
          this.selectedBusiness = response.data.business;
        })
        .catch(() => {
          this.message = 'Failed to fetch business details.';
          this.clearMessage();
        });
    },
  },
};
</script>

<style scoped>
.admin-business {
  max-width: 900px;
  margin: 2rem auto;
  padding: 1rem;
  font-family: Arial, sans-serif;
}

h2 {
  text-align: center;
  margin-bottom: 1.5rem;
}

.message {
  background: #e0f7fa;
  border: 1px solid #26c6da;
  padding: 10px;
  margin-bottom: 20px;
  border-radius: 4px;
  color: #006064;
}

.business-form {
  background: #fafafa;
  padding: 20px;
  border-radius: 6px;
  border: 1px solid #ddd;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
}

.form-actions {
  margin-top: 10px;
}

input,
select {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  box-sizing: border-box;
  border: 1px solid #ccc;
  border-radius: 4px;
}

button {
  padding: 6px 12px;
  border: none;
  cursor: pointer;
  border-radius: 4px;
}

.btn-primary {
  background-color: #2e7d32;
  color: white;
}

.btn-secondary {
  background-color: #999;
  color: white;
  margin-left: 10px;
}

.btn-small {
  background-color: #1976d2;
  color: white;
  margin-right: 5px;
}

.btn-danger {
  background-color: #c62828;
  color: white;
}

.btn-info {
  background-color: #0288d1;
  color: white;
  margin-right: 5px;
}

.business-table {
  width: 100%;
  border-collapse: collapse;
}

.business-table th,
.business-table td {
  padding: 10px;
  border: 1px solid #ccc;
  text-align: left;
}

.error-message {
  color: #d32f2f;
  font-size: 0.9em;
  margin-top: 5px;
}

.business-details {
  background: #f1f8e9;
  padding: 20px;
  border: 1px solid #c5e1a5;
  margin: 20px 0;
  border-radius: 5px;
}
</style>
