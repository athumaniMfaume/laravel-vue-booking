<template>
  <div class="container">
    <h2>User Reviews</h2>

    <div v-if="loading" class="info">Loading reviews...</div>
    <div v-if="error" class="error">{{ error }}</div>
    <div v-if="success" class="success">{{ success }}</div>

    <table v-if="reviews.length" class="reviews-table">
      <thead>
        <tr>
          <th>Business</th>
          <th>Review</th>
          <th>Stars</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="review in reviews" :key="review.id">
          <td>{{ getBusinessName(review.business_id) }}</td>
          <td>{{ review.review }}</td>
          <td>{{ review.stars }}</td>
          <td>
            <button @click="showReview(review.id)" class="view-btn">View</button>
            <button @click="editReview(review)" class="edit-btn">Edit</button>
            <button @click="deleteReview(review.id)" class="delete-btn">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else class="info">No reviews found.</div>

    <!-- Review Details Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <h3>Review Details</h3>
        <p><strong>Business:</strong> {{ getBusinessName(selectedReview.business_id) }}</p>
        <p><strong>Review:</strong> {{ selectedReview.review }}</p>
        <p><strong>Stars:</strong> {{ selectedReview.stars }}</p>
        <button class="close-modal-btn" @click="closeModal">Close</button>
      </div>
    </div>

    <div class="form-section">
      <h3>{{ editMode ? 'Edit Review' : 'Add Review' }}</h3>
      <form @submit.prevent="submitReview">
        <label>
          Business:
          <select v-model.number="form.business_id">
            <option value="" disabled>Select a business</option>
            <option v-for="business in businesses" :key="business.id" :value="business.id">
              {{ business.name }}
            </option>
          </select>
        </label>
        <div v-if="validationErrors.business_id" class="input-error">
          {{ validationErrors.business_id[0] }}
        </div>

        <label>
          Review:
          <textarea v-model="form.review" placeholder="Write your review here..."></textarea>
        </label>
        <div v-if="validationErrors.review" class="input-error">
          {{ validationErrors.review[0] }}
        </div>

        <label>
          Stars:
          <select v-model.number="form.stars">
            <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
          </select>
        </label>
        <div v-if="validationErrors.stars" class="input-error">
          {{ validationErrors.stars[0] }}
        </div>

        <div class="form-actions">
          <button type="submit" class="submit-btn">{{ editMode ? 'Update' : 'Add' }}</button>
          <button v-if="editMode" @click="cancelEdit" type="button" class="cancel-btn">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import api from '@/utils/axios';

export default {
  data() {
    return {
      reviews: [],
      businesses: [],
      loading: false,
      error: null,
      success: null,
      editMode: false,
      showModal: false,
      selectedReview: {},
      form: {
        id: null,
        business_id: null,
        review: '',
        stars: 5,
      },
      validationErrors: {},
    };
  },
  mounted() {
    this.fetchBusinesses();
    this.fetchReviews();
  },
  methods: {
    async fetchBusinesses() {
      try {
        const response = await api.get('/user/businesses');
        this.businesses = response.data.businesses;
      } catch (err) {
        this.error = 'Failed to load businesses.';
      }
    },
    async fetchReviews() {
      this.loading = true;
      this.error = null;
      this.success = null;
      try {
        const response = await api.get('/user/reviews');
        this.reviews = response.data;
      } catch (err) {
        this.error = err.response?.data?.error || 'Failed to load reviews.';
      } finally {
        this.loading = false;
      }
    },
    getBusinessName(businessId) {
      const business = this.businesses.find(b => b.id === businessId);
      return business ? business.name : 'Unknown';
    },
    async showReview(id) {
      this.error = null;
      try {
        const response = await api.get(`/user/reviews/${id}`);
        this.selectedReview = response.data;
        this.showModal = true;
      } catch (err) {
        this.error = err.response?.data?.error || 'Failed to fetch review.';
      }
    },
    closeModal() {
      this.showModal = false;
      this.selectedReview = {};
    },
    editReview(review) {
      this.editMode = true;
      this.form = { ...review };
      this.error = null;
      this.success = null;
      this.validationErrors = {};
    },
    cancelEdit() {
      this.editMode = false;
      this.form = {
        id: null,
        business_id: null,
        review: '',
        stars: 5,
      };
      this.error = null;
      this.success = null;
      this.validationErrors = {};
    },
    async submitReview() {
      this.error = null;
      this.success = null;
      this.validationErrors = {};
      try {
        let response;
        if (this.editMode) {
          response = await api.put(`/user/reviews/${this.form.id}`, this.form);
        } else {
          response = await api.post('/user/reviews', this.form);
        }
        this.success = response.data.message || 'Operation successful!';
        this.cancelEdit();
        this.fetchReviews();
      } catch (err) {
        if (err.response?.status === 400 && err.response.data.errors) {
          this.validationErrors = err.response.data.errors;
        } else {
          this.error = err.response?.data?.error || 'Operation failed.';
        }
      }
    },
    async deleteReview(id) {
      if (!confirm('Are you sure you want to delete this review?')) return;

      this.error = null;
      this.success = null;

      try {
        const response = await api.delete(`/user/reviews/${id}`);
        this.success = response.data.message || 'Review deleted successfully!';
        this.fetchReviews();
      } catch (err) {
        this.error = err.response?.data?.error || 'Failed to delete review.';
      }
    },
  },
};
</script>

<style scoped>
.container {
  max-width: 800px;
  margin: auto;
  padding: 1rem;
  font-family: 'Arial', sans-serif;
}

h2, h3 {
  color: #2c3e50;
}

.reviews-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}

.reviews-table th,
.reviews-table td {
  border: 1px solid #ddd;
  padding: 0.75rem 1rem;
  text-align: left;
}

.reviews-table th {
  background-color: #2c3e50;
  color: white;
  font-weight: bold;
}

.reviews-table tbody tr:nth-child(even) {
  background-color: #f9f9f9;
}

.edit-btn,
.delete-btn,
.view-btn {
  margin-right: 0.5rem;
  padding: 0.3rem 0.6rem;
  font-size: 0.9rem;
  border-radius: 4px;
  cursor: pointer;
  border: none;
}

.edit-btn {
  background-color: #3498db;
  color: white;
}

.view-btn {
  background-color: #8e44ad;
  color: white;
}

.delete-btn {
  background-color: #e74c3c;
  color: white;
}

.form-section {
  margin-top: 2rem;
  background-color: #f4f4f4;
  padding: 1rem;
  border-radius: 6px;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
}

select,
textarea {
  width: 100%;
  padding: 0.5rem;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

textarea {
  resize: vertical;
}

.input-error {
  color: red;
  font-size: 0.85rem;
  margin-top: -0.5rem;
  margin-bottom: 0.8rem;
}

.error {
  color: red;
  margin-bottom: 1rem;
}

.success {
  color: green;
  margin-bottom: 1rem;
}

.info {
  color: #555;
  font-style: italic;
  margin-bottom: 1rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
}

.submit-btn,
.cancel-btn {
  padding: 0.5rem 1rem;
  font-size: 1rem;
  border-radius: 4px;
  cursor: pointer;
  border: none;
}

.submit-btn {
  background-color: #2ecc71;
  color: white;
}

.cancel-btn {
  background-color: #95a5a6;
  color: white;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
}

.close-modal-btn {
  margin-top: 1rem;
  background-color: #3498db;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 5px;
  cursor: pointer;
}
</style>
