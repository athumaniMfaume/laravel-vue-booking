<template>
  <div class="booking-container">
    <h2>User Bookings</h2>

    <div v-if="loading" class="loading">Loading bookings...</div>

    <table v-if="bookings.length" class="booking-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Service ID</th>
          <th>Opening Hours</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(booking, index) in bookings" :key="booking.id">
          <td>{{ index + 1 }}</td>
          <td>{{ booking.service_id }}</td>
          <td>{{ formatDate(booking.opening_hours) }}</td>
          <td>
            <button @click="viewBooking(booking.id)" class="btn view-btn">View</button>
            <button @click="editBooking(booking)" class="btn edit-btn">Edit</button>
            <button @click="deleteBooking(booking.id)" class="btn delete-btn">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else class="no-bookings">No bookings found.</div>

    <div v-if="selectedBooking" class="selected-booking">
      <h3>Booking Details</h3>
      <p><strong>ID:</strong> {{ selectedBooking.id }}</p>
      <p><strong>Service ID:</strong> {{ selectedBooking.service_id }}</p>
      <p><strong>Opening Hours:</strong> {{ formatDate(selectedBooking.opening_hours) }}</p>
      <p><strong>Service Name:</strong> {{ selectedBooking.service?.name || 'N/A' }}</p>
      <button @click="selectedBooking = null" class="btn cancel-btn">Close</button>
    </div>

    <h3 class="form-title">{{ editMode ? 'Edit Booking' : 'Add Booking' }}</h3>

    <form @submit.prevent="submitBooking" class="booking-form" novalidate>
      <div class="form-group">
        <label for="service_id">Service ID:</label>
        <input
          id="service_id"
          type="number"
          v-model.number="form.service_id"
          placeholder="Enter service ID"
        />
        <div v-if="validationErrors.service_id" class="input-error">
          {{ validationErrors.service_id[0] }}
        </div>
      </div>

      <div class="form-group">
        <label for="opening_hours">Opening Hours:</label>
        <input
          id="opening_hours"
          type="datetime-local"
          v-model="form.opening_hours"
          placeholder="Select opening time"
        />
        <div v-if="validationErrors.opening_hours" class="input-error">
          {{ validationErrors.opening_hours[0] }}
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn submit-btn">
          {{ editMode ? 'Update Booking' : 'Add Booking' }}
        </button>
        <button
          v-if="editMode"
          @click="cancelEdit"
          type="button"
          class="btn cancel-btn"
        >
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import api from '@/utils/axios';

export default {
  data() {
    return {
      bookings: [],
      selectedBooking: null,
      loading: false,
      editMode: false,
      validationErrors: {},
      form: {
        id: null,
        service_id: null,
        opening_hours: '',
      },
    };
  },
  mounted() {
    this.fetchBookings();
  },
  methods: {
    async fetchBookings() {
      this.loading = true;
      try {
        const response = await api.get('/user/bookings');
        this.bookings = response.data;
      } catch (err) {
        console.error('Failed to fetch bookings:', err);
      } finally {
        this.loading = false;
      }
    },
    async viewBooking(id) {
      try {
        const response = await api.get(`/user/bookings/${id}`);
        this.selectedBooking = response.data;
      } catch (err) {
        console.error('Failed to fetch booking:', err);
        alert('Booking not found or unauthorized.');
      }
    },
    formatDate(dateStr) {
      const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      };
      return new Date(dateStr).toLocaleString(undefined, options);
    },
    editBooking(booking) {
      this.editMode = true;
      this.validationErrors = {};
      const dt = new Date(booking.opening_hours);
      this.form = { ...booking, opening_hours: dt.toISOString().slice(0, 16) };
    },
    cancelEdit() {
      this.editMode = false;
      this.validationErrors = {};
      this.form = {
        id: null,
        service_id: null,
        opening_hours: '',
      };
    },
    async submitBooking() {
      this.validationErrors = {};
      try {
        if (this.editMode) {
          await api.put(`/user/bookings/${this.form.id}`, this.form);
        } else {
          await api.post('/user/bookings', this.form);
        }
        this.cancelEdit();
        this.fetchBookings();
      } catch (err) {
        if (err.response?.status === 422 && err.response.data.errors) {
          this.validationErrors = err.response.data.errors;
        } else {
          console.error('Submission failed:', err);
        }
      }
    },
    async deleteBooking(id) {
      if (!confirm('Are you sure you want to delete this booking?')) return;
      try {
        await api.delete(`/user/bookings/${id}`);
        this.fetchBookings();
      } catch (err) {
        console.error('Deletion failed:', err);
      }
    },
  },
};
</script>

<style scoped>
.booking-container {
  max-width: 800px;
  margin: 2rem auto;
  padding: 1.5rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
}

.loading {
  font-style: italic;
  color: #666;
  margin-bottom: 1rem;
}

.booking-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2rem;
}

.booking-table th,
.booking-table td {
  border: 1px solid #ccc;
  padding: 0.75rem;
  text-align: left;
}

.booking-table th {
  background-color: #f0f0f0;
}

.booking-table tr:nth-child(even) {
  background-color: #fafafa;
}

.btn {
  padding: 0.4rem 0.8rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.85rem;
  margin-right: 0.5rem;
}

.view-btn {
  background-color: #2196f3;
  color: white;
}
.view-btn:hover {
  background-color: #1976d2;
}

.edit-btn {
  background-color: #4caf50;
  color: white;
}
.edit-btn:hover {
  background-color: #388e3c;
}

.delete-btn {
  background-color: #f44336;
  color: white;
}
.delete-btn:hover {
  background-color: #d32f2f;
}

.no-bookings {
  font-style: italic;
  color: #777;
  margin-bottom: 2rem;
  text-align: center;
}

.form-title {
  margin-bottom: 1rem;
  font-weight: bold;
  border-bottom: 2px solid #4caf50;
  padding-bottom: 0.25rem;
  color: #2e7d32;
}

.booking-form {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

label {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

input[type='number'],
input[type='datetime-local'] {
  padding: 0.5rem;
  border: 1px solid #bbb;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

input[type='number']:focus,
input[type='datetime-local']:focus {
  outline: none;
  border-color: #4caf50;
}

.input-error {
  color: #d32f2f;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
}

.submit-btn {
  background-color: #4caf50;
  color: white;
}

.cancel-btn {
  background-color: #9e9e9e;
  color: white;
}

.submit-btn:hover {
  background-color: #2e7d32;
}

.cancel-btn:hover {
  background-color: #616161;
}

.selected-booking {
  border: 1px solid #2196f3;
  padding: 1rem;
  border-radius: 5px;
  margin-bottom: 2rem;
  background-color: #e3f2fd;
}
</style>
