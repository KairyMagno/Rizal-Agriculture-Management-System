async function fetchCategoriesNav() {
  try {
      // Replace '/get-categories' with your actual endpoint to fetch data
      const response = await fetch('../includes/fetch_categories.php');
      const data = await response.json();

      // Get the container where the categories will be displayed
      const container = document.getElementById('dropdown-container');

      // Clear any existing content
      container.innerHTML = '';

      // Loop through the data and dynamically create HTML
      data.forEach(category => {
          const categoryHtml = `
              <li><a href="multimedia.php#${category.slug}">${category.name}</a></li>
          `;
          container.innerHTML += categoryHtml;
      });
  } catch (error) {
      console.error('Error fetching categories:', error);
  }
}

async function init() {
  await fetchCategoriesNav();
}

init();

document.addEventListener('DOMContentLoaded', function() {
  // Get all the edit links
  const editLinks = document.querySelectorAll('.edit-btn a');
  const editModal = document.getElementById('editCategoryModal');

  editLinks.forEach(link => {
      link.addEventListener('click', function(event) {
          event.preventDefault(); // Prevent the default link behavior

          const categoryId = this.getAttribute('href').split('=')[1]; // Extract ID from URL
          const categoryName = this.closest('tr').querySelector('td:nth-child(1)').textContent;
          const categorySlug = this.closest('tr').querySelector('td:nth-child(2)').textContent;

          // Set the form values
          document.getElementById('edit_category_id').value = categoryId;
          document.getElementById('edit_category_name').value = categoryName;
          document.getElementById('edit_category_slug').value = categorySlug;

          // Display the modal
          editModal.style.display = 'block';
      });
  });

  // Close the modal when clicking outside the modal content (optional)
  window.addEventListener('click', function(event) {
      if (event.target === editModal) {
          editModal.style.display = 'none';
      }
  });
});

// Function to close the "Edit Category" modal
document.getElementById('closeEditModal').addEventListener('click', function() {
  document.getElementById('editCategoryModal').style.display = 'none';
});

function showModal(modalId) {
  document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

function submitForm(formId) {
  // Submit the specified form
  document.getElementById(formId).submit();
  closeModal(formId === 'termsForm' ? 'termsConfirmationModal' : 'privacyConfirmationModal');
}

const modal = document.getElementById('addCategoryModal');
// Get open modal button
const openModalBtn = document.getElementById('addCategoryBtn');
// Get close modal button
const closeModalBtn = document.getElementById('closeModal');

// Event to open the modal
openModalBtn.addEventListener('click', () => {
  modal.style.display = 'block';
});

// Event to close the modal
closeModalBtn.addEventListener('click', () => {
  modal.style.display = 'none';
});

// Close the modal when clicking outside of it
window.addEventListener('click', (event) => {
  if (event.target === modal) {
      modal.style.display = 'none';
  }
});


function resetContent(textareaId) {
  const textarea = document.getElementById(textareaId);
  textarea.value = textarea.getAttribute('data-original-content');
}

document.querySelector('#cancelButtonTerms').addEventListener('click', function() {
  const textarea = document.querySelector('textarea[name="terms_content"]');
  const originalContent = textarea.getAttribute('data-original-content');
  textarea.value = originalContent; // Reset the content to the original
});

document.querySelector('#cancelButtonData').addEventListener('click', function() {
  const textarea = document.querySelector('textarea[name="privacy_content"]');
  const originalContent = textarea.getAttribute('data-original-content');
  textarea.value = originalContent; // Reset the content to the original
});

const termsModal = document.getElementById('editModalTerms');
const policiesModal = document.getElementById('editModalPolicies');
const termsModalOverlay = document.getElementById('modalTermsOverlay');
const policiesModalOverlay = document.getElementById('modalPoliciesOverlay');

// Terms modal functions
function openTermsModal() {
  termsModal.style.display = 'block';
  termsModalOverlay.style.display = "block";
}

function closeTermsModal() {
  termsModal.style.display = 'none';
  termsModalOverlay.style.display = "none";
}

// Policies modal functions
function openPoliciesModal() {
  policiesModal.style.display = 'block';
  policiesModalOverlay.style.display = 'block';
}

function closePoliciesModal() {
  policiesModal.style.display = 'none';
  policiesModalOverlay.style.display = 'none';
}

function logout() {
  // Redirect to the logout PHP script
  window.location.href = '../includes/logout.php'; // Ensure this path matches the location of logout.php
}

// JavaScript to handle modal display
function openFaqModal() {
  document.getElementById('faqModal').style.display = 'block';
  document.getElementById('modalFaqOverlay').style.display = 'block';
}
  function closeFaqModal() {
  document.getElementById('faqModal').style.display = 'none';
  document.getElementById('modalFaqOverlay').style.display = 'none';
}

document.getElementById('editMediaButton').addEventListener('click', function() {
  document.getElementById('editMediaModal').style.display = 'block';
  document.getElementById('mediaModalBackground').style.display = 'block';
  });

  document.getElementById('closeButton').addEventListener('click', function() {
  document.getElementById('editMediaModal').style.display = 'none';
  document.getElementById('mediaModalBackground').style.display = 'none';
  });

  document.getElementById('mediaModalBackground').addEventListener('click', function() {
  document.getElementById('editMediaModal').style.display = 'none';
  document.getElementById('mediaModalBackground').style.display = 'none';
  });

  function openEditFaqModal(faqId, question, answer) {
    document.getElementById('editFaqId').value = faqId;
    document.getElementById('editQuestion').value = question;
    document.getElementById('editAnswer').value = answer;
  
    document.getElementById('editFaqModal').style.display = 'block';
    document.getElementById('modalFaqOverlay').style.display = 'block';
  }
  
  function closeEditFaqModal() {
    document.getElementById('editFaqModal').style.display = 'none';
    document.getElementById('modalFaqOverlay').style.display = 'none';
  }
  
  function confirmDeleteFaq(faqId) {
    const userConfirmed = confirm('Are you sure you want to delete this FAQ?');
    if (userConfirmed) {
        // Redirect to the PHP script to delete the FAQ
        window.location.href = 'delete_faq.php?faq_id=' + faqId;
    }
}  

function openModalCompany(id, email, companyName, password) {
  document.getElementById('modalIdCompany').value = id;
  document.getElementById('modalEmailCompany').value = email;
  document.getElementById('modalCompanyName').value = companyName;
  document.getElementById('modalPasswordCompany').value = password;
  
  document.getElementById('modalFormCompany').style.display = 'block';
  document.getElementById('modalOverlayCompany').style.display = 'block';
}

function closeModalCompany() {
  document.getElementById('modalFormCompany').style.display = 'none';
  document.getElementById('modalOverlayCompany').style.display = 'none';
}