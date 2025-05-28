<template>
  <div class="business-service">
    <h2>Business Services</h2>

    <div v-if="message" class="message">{{ message }}</div>

    <!-- Add/Edit form -->
    <form @submit.prevent="isEditing ? updateService() : addService()">
      <div>
        <label for="name">Name:</label>
        <input id="name" v-model="form.name" type="text" />
        <div v-if="errors.name" class="error-message">
          <span v-for="(error, i) in errors.name" :key="i">{{ error }}</span>
        </div>
      </div>

      <div>
        <label for="description">Description:</label>
        <textarea id="description" v-model="form.description"></textarea>
        <div v-if="errors.description" class="error-message">
          <span v-for="(error, i) in errors.description" :key="i">{{ error }}</span>
        </div>
      </div>

      <div>
        <label for="price">Price (Tsh):</label>
        <input id="price" v-model.number="form.price" type="number" min="0" step="0.01" />
        <div v-if="errors.price" class="error-message">
          <span v-for="(error, i) in errors.price" :key="i">{{ error }}</span>
        </div>
      </div>

      <button type="submit">{{ isEditing ? 'Update' : 'Add' }} Service</button>
      <button type="button" @click="resetForm" v-if="isEditing">Cancel</button>
    </form>

    <hr />

    <!-- Services list -->
    <table border="1" cellspacing="0" cellpadding="5">
      <thead>
        <tr>
          <th>Name</th>
          <th>Description</th>
          <th>Price (Tsh)</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="service in services" :key="service.id">
          <td>{{ service.name }}</td>
          <td>{{ service.description }}</td>
          <td>{{ service.price.toLocaleString() }}</td>
          <td>
            <button @click="editService(service)">Edit</button>
            <button @click="deleteService(service.id)">Delete</button>
            <button @click="showService(service.id)">View</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- View Modal -->
    <div v-if="viewedService" class="modal">
      <div class="modal-content">
        <span class="close" @click="viewedService = null">&times;</span>
        <h3>Service Details</h3>
        <p><strong>Name:</strong> {{ viewedService.name }}</p>
        <p><strong>Description:</strong> {{ viewedService.description }}</p>
        <p><strong>Price:</strong> {{ viewedService.price.toLocaleString() }} Tsh</p>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/utils/axios';

export default {
  data() {
    return {
      services: [],
      form: {
        id: null,
        name: '',
        description: '',
        price: null,
      },
      isEditing: false,
      message: '',
      errors: {},
      viewedService: null,
    };
  },
  created() {
    this.fetchServices();
  },
  methods: {
    fetchServices() {
      api.get('/business/services')
        .then(res => {
          this.services = res.data.services || [];
        })
        .catch(() => {
          this.message = 'Failed to load services.';
          this.clearMessage();
        });
    },

    showService(id) {
      this.viewedService = null;
      api.get(`/business/services/${id}`)
        .then(res => {
          this.viewedService = res.data.service;
        })
        .catch(() => {
          this.message = 'Failed to load service details.';
          this.clearMessage();
        });
    },

    addService() {
      this.errors = {};
      api.post('/business/services', this.form)
        .then(res => {
          this.message = res.data.message;
          this.fetchServices();
          this.resetForm();
          this.clearMessage();
        })
        .catch(err => {
          if (err.response && err.response.status === 422) {
            this.errors = err.response.data.errors;
          } else {
            this.message = 'Failed to add service.';
            this.clearMessage();
          }
        });
    },

    updateService() {
      this.errors = {};
      api.put(`/business/services/${this.form.id}`, this.form)
        .then(res => {
          this.message = res.data.message;
          this.fetchServices();
          this.resetForm();
          this.clearMessage();
        })
        .catch(err => {
          if (err.response && err.response.status === 422) {
            this.errors = err.response.data.errors;
          } else {
            this.message = 'Failed to update service.';
            this.clearMessage();
          }
        });
    },

    deleteService(id) {
      if (!confirm('Are you sure you want to delete this service?')) return;
      api.delete(`/business/services/${id}`)
        .then(res => {
          this.message = res.data.message;
          this.fetchServices();
          this.clearMessage();
        })
        .catch(() => {
          this.message = 'Failed to delete service.';
          this.clearMessage();
        });
    },

    editService(service) {
      this.isEditing = true;
      this.form = { ...service };
    },

    resetForm() {
      this.isEditing = false;
      this.form = {
        id: null,
        name: '',
        description: '',
        price: null,
      };
      this.errors = {};
    },

    clearMessage() {
      setTimeout(() => {
        this.message = '';
      }, 3000);
    },
  },
};
</script>

<style scoped>
.business-service {
  max-width: 800px;
  margin: 0 auto;
}

.message {
  margin: 10px 0;
  padding: 10px;
  background: #eef;
  border: 1px solid #99c;
  border-radius: 5px;
}

form > div {
  margin-bottom: 10px;
}

input[type='text'],
input[type='number'],
textarea,
select {
  width: 100%;
  padding: 6px 8px;
  border: 1px solid #ccc;
  border-radius: 3px;
  box-sizing: border-box;
}

button {
  margin-right: 5px;
  padding: 6px 12px;
  border: none;
  background-color: #1976d2;
  color: white;
  border-radius: 3px;
  cursor: pointer;
}

button:hover {
  background-color: #155a9c;
}

.error-message {
  color: red;
  font-size: 0.9em;
  margin-top: 3px;
}

.error-message span {
  display: block;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

th,
td {
  padding: 8px;
  border: 1px solid #ccc;
  text-align: left;
}

th {
  background-color: #f0f0f0;
}

/* Modal styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  width: 400px;
  position: relative;
}

.close {
  position: absolute;
  top: 8px;
  right: 12px;
  font-size: 20px;
  cursor: pointer;
  color: #333;
}
</style>
