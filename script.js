document.addEventListener('DOMContentLoaded', () => {
    const contactForm = document.getElementById('new-contact-form');
    const contactsTableBody = document.querySelector('#contacts-table tbody');
    const submitButton = contactForm.querySelector('button[type="submit"]');

    let editingContactId = null; // To track the ID of the contact being edited

    const fetchContacts = async () => {
        try {
            const response = await fetch('/api/contacts');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contacts = await response.json();
            displayContacts(contacts);
        } catch (error) {
            console.error('Error fetching contacts:', error);
        }
    };

    const displayContacts = (contacts) => {
        contactsTableBody.innerHTML = '';
        contacts.forEach(contact => {
            const row = contactsTableBody.insertRow();
            row.insertCell().textContent = contact.id;
            row.insertCell().textContent = contact.name;
            row.insertCell().textContent = contact.phone_number || '';
            row.insertCell().textContent = contact.email || '';
            row.insertCell().textContent = contact.address || '';
            const actionsCell = row.insertCell();
            actionsCell.classList.add('actions-buttons');
            actionsCell.innerHTML = `
                <button class="button edit" onclick="editContact(${contact.id})">Edit</button>
                <button class="button delete" onclick="deleteContact(${contact.id})">Delete</button>
            `;
        });
    };

    contactForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = new FormData(contactForm);
        const contactData = Object.fromEntries(formData.entries());

        const method = editingContactId ? 'PUT' : 'POST';
        const url = editingContactId ? `/api/contacts/${editingContactId}` : '/api/contacts';
        const message = editingContactId ? 'Contact updated:' : 'Contact added:';
        const errorMessage = editingContactId ? 'Error updating contact:' : 'Error adding contact:';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(contactData),
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log(message, result);
            contactForm.reset();
            editingContactId = null;
            if (submitButton) {
                submitButton.textContent = 'Add Contact';
            }
            fetchContacts();
        } catch (error) {
            console.error(errorMessage, error);
        }
    });

    window.editContact = async (id) => {
        editingContactId = id;
        try {
            const response = await fetch(`/api/contacts/${id}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contact = await response.json();
            document.getElementById('name').value = contact.name;
            document.getElementById('phone_number').value = contact.phone_number || '';
            document.getElementById('email').value = contact.email || '';
            document.getElementById('address').value = contact.address || '';
            if (submitButton) {
                submitButton.textContent = 'Update Contact';
            }
        } catch (error) {
            console.error('Error fetching contact for edit:', error);
        }
    };

    window.deleteContact = async (id) => {
        if (confirm('Are you sure you want to delete this contact?')) {
            try {
                const response = await fetch(`/api/contacts/${id}`, {
                    method: 'DELETE',
                });
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const result = await response.json();
                console.log('Contact deleted:', result);
                fetchContacts();
            } catch (error) {
                console.error('Error deleting contact:', error);
            }
        }
    };

    fetchContacts(); // Initial load
});
