function getUsers() {
  return JSON.parse(localStorage.getItem('users')) || [];
}

function saveUsers(users) {
  localStorage.setItem('users', JSON.stringify(users));
}

function getFeedbacks() {
  return JSON.parse(localStorage.getItem('feedbacks')) || [];
}

function saveFeedbacks(feedbacks) {
  localStorage.setItem('feedbacks', JSON.stringify(feedbacks));
}

function seedDefaultData() {
  const users = getUsers();
  const hasAdmin = users.some(user => user.email === 'admin@rcjy.edu.sa');

  if (!hasAdmin) {
    users.push({
      id: Date.now(),
      name: 'YIC Admin',
      email: 'admin@rcjy.edu.sa',
      password: 'Admin123',
      role: 'admin'
    });
    saveUsers(users);
  }
}

function getCurrentUser() {
  return JSON.parse(localStorage.getItem('currentUser'));
}

function setCurrentUser(user) {
  localStorage.setItem('currentUser', JSON.stringify(user));
  localStorage.setItem('role', user.role);
}

function requireLogin(expectedRole) {
  const user = getCurrentUser();

  if (!user) {
    alert('Please login first.');
    window.location.href = 'login.html';
    return false;
  }

  if (expectedRole && user.role !== expectedRole) {
    alert('You are not allowed to open this page.');
    if (user.role === 'admin') {
      window.location.href = 'admin_dashboard.html';
    } else {
      window.location.href = 'student_dashboard.html';
    }
    return false;
  }

  return true;
}

function logout() {
  localStorage.removeItem('currentUser');
  localStorage.removeItem('role');
  window.location.href = 'index.html';
}

function markActiveLinks() {
  const currentPage = window.location.pathname.split('/').pop();
  const links = document.querySelectorAll('#navbar a');

  links.forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPage) {
      link.classList.add('active');
    }
  });
}

function loadNavbar() {
  const user = getCurrentUser();
  const nav = document.getElementById('navbar');

  if (!nav || !user) return;

  if (user.role === 'admin') {
    nav.innerHTML = `
      <header class="site-header">
        <div class="container header-content">
          <div class="logo-box">
            <img src="logo.png.png" alt="YIC Logo" class="logo-img">
            <div class="brand-text">
              <h2>YIC Admin Panel</h2>
              <p class="header-subtitle">Manage student feedback records</p>
            </div>
          </div>
          <nav class="header-nav">
            <a href="admin_dashboard.html">Dashboard</a>
            <a href="admin_feedbacks.html">Manage Feedback</a>
            <a href="#" onclick="logout()">Logout</a>
          </nav>
        </div>
      </header>
    `;
  } else {
    nav.innerHTML = `
      <header class="site-header">
        <div class="container header-content">
          <div class="logo-box">
            <img src="logo.png.png" alt="YIC Logo" class="logo-img">
            <div class="brand-text">
              <h2>YIC Feedback System</h2>
              <p class="header-subtitle">Welcome, ${user.name}</p>
            </div>
          </div>
          <nav class="header-nav">
            <a href="student_dashboard.html">Dashboard</a>
            <a href="submit_feedback.html">Submit Feedback</a>
            <a href="view_feedback.html">My Feedback</a>
            <a href="#" onclick="logout()">Logout</a>
          </nav>
        </div>
      </header>
    `;
  }

  markActiveLinks();
}

function goTo(page) {
  window.location.href = page;
}

function registerUser(event) {
  event.preventDefault();

  const name = document.getElementById('registerName').value.trim();
  const email = document.getElementById('registerEmail').value.trim().toLowerCase();
  const password = document.getElementById('registerPassword').value;
  const confirmPassword = document.getElementById('confirmPassword').value;

  if (name.length < 3) {
    alert('Name must be at least 3 characters.');
    return false;
  }

  if (!email.endsWith('@rcjy.edu.sa')) {
    alert('Email must be in this format: example@rcjy.edu.sa');
    return false;
  }

  if (password.length < 6) {
    alert('Password must be at least 6 characters.');
    return false;
  }

  if (password !== confirmPassword) {
    alert('Passwords do not match.');
    return false;
  }

  const users = getUsers();
  const exists = users.some(user => user.email === email);

  if (exists) {
    alert('This email is already registered.');
    return false;
  }

  const user = {
    id: Date.now(),
    name: name,
    email: email,
    password: password,
    role: 'student'
  };

  users.push(user);
  saveUsers(users);

  alert('Account created successfully. You can login now.');
  window.location.href = 'login.html';
  return false;
}


function loginUser(event) {
  event.preventDefault();

  const email = document.getElementById('loginEmail').value.trim().toLowerCase();
  const password = document.getElementById('loginPassword').value;

  if (!email.endsWith('@rcjy.edu.sa')) {
    alert('Email must be in this format: example@rcjy.edu.sa');
    return false;
  }

  let users = getUsers();

  const adminExists = users.some(user => user.role === 'admin');

  if (!adminExists) {
    const adminUser = {
      id: 1,
      name: 'Admin',
      email: 'admin@rcjy.edu.sa',
      password: 'Admin123',
      role: 'admin'
    };

    users.push(adminUser);
    saveUsers(users);
  }

  users = getUsers();

  const user = users.find(item =>
    item.email === email &&
    item.password === password
  );

  if (!user) {
    alert('Invalid email or password.');
    return false;
  }

  setCurrentUser(user);

  if (user.role === 'admin') {
    window.location.href = 'admin_dashboard.html';
  } else {
    window.location.href = 'student_dashboard.html';
  }

  return false;
}
function loadDashboard() {
  const user = getCurrentUser();
  if (!user || user.role !== 'student') return;

  const feedbacks = getFeedbacks().filter(item => item.userEmail === user.email);

  const total = feedbacks.length;
  const pending = feedbacks.filter(item => item.status === 'Pending').length;
  const reviewed = feedbacks.filter(item => item.status === 'Reviewed').length;
  const resolved = feedbacks.filter(item => item.status === 'Resolved').length;

  document.getElementById('totalCount').innerText = total;
  document.getElementById('pendingCount').innerText = pending;
  document.getElementById('reviewedCount').innerText = reviewed;
  document.getElementById('resolvedCount').innerText = resolved;

  const list = document.getElementById('feedbackList');
  list.innerHTML = '';

  if (feedbacks.length === 0) {
    list.innerHTML = '<p>No feedback submitted yet.</p>';
    return;
  }

  feedbacks.slice().reverse().slice(0, 3).forEach(item => {
    list.innerHTML += `
      <div class="mini-card">
        <div>
          <strong>${item.title}</strong>
          <p class="small-text">${item.category}</p>
        </div>
        <span class="status-badge ${item.status.toLowerCase()}">${item.status}</span>
      </div>
    `;
  });
}

function submitFeedback(event) {
  event.preventDefault();

  const user = getCurrentUser();
  if (!user) return false;

  const title = document.getElementById('feedbackTitle').value.trim();
  const category = document.getElementById('feedbackCategory').value;
  const message = document.getElementById('feedbackMessage').value.trim();

  if (title.length < 5) {
    alert('Title must be at least 5 characters.');
    return false;
  }

  if (message.length < 15) {
    alert('Feedback message must be at least 15 characters.');
    return false;
  }

  const feedbacks = getFeedbacks();

  feedbacks.push({
    id: Date.now(),
    userEmail: user.email,
    studentName: user.name,
    title: title,
    category: category,
    message: message,
    status: 'Pending',
    response: '',
    createdAt: new Date().toLocaleDateString()
  });

  saveFeedbacks(feedbacks);

  alert('Feedback submitted successfully.');
  window.location.href = 'view_feedback.html';
  return false;
}

function loadMyFeedback() {
  const user = getCurrentUser();
  if (!user || user.role !== 'student') return;

  const feedbacks = getFeedbacks().filter(item => item.userEmail === user.email);
  const container = document.getElementById('myFeedbackList');
  container.innerHTML = '';

  if (feedbacks.length === 0) {
    container.innerHTML = '<div class="card"><p>No feedback submitted yet.</p></div>';
    return;
  }

  feedbacks.slice().reverse().forEach(item => {
    const canEdit = item.status === 'Pending';

    container.innerHTML += `
      <div class="card">
        <div class="card-top">
          <h3>${item.title}</h3>
          <span class="status-badge ${item.status.toLowerCase()}">${item.status}</span>
        </div>
        <p><strong>Category:</strong> ${item.category}</p>
        <p><strong>Date:</strong> ${item.createdAt}</p>
        <p><strong>Message:</strong> ${item.message}</p>
        <p><strong>Admin Response:</strong> ${item.response ? item.response : 'Waiting for response...'}</p>
        <div class="action-row">
          ${canEdit
            ? `<button onclick="editFeedback(${item.id})">Edit</button>
               <button class="danger-btn" onclick="deleteFeedback(${item.id})">Delete</button>`
            : `<span class="small-text">You can edit or delete only pending feedback.</span>`
          }
        </div>
      </div>
    `;
  });
}

function editFeedback(id) {
  const feedbacks = getFeedbacks();
  const item = feedbacks.find(feedback => feedback.id === id);

  if (!item || item.status !== 'Pending') {
    alert('Only pending feedback can be edited.');
    return;
  }

  const newTitle = prompt('Edit title:', item.title);
  if (newTitle === null) return;

  const newMessage = prompt('Edit message:', item.message);
  if (newMessage === null) return;

  if (newTitle.trim().length < 5) {
    alert('Title must be at least 5 characters.');
    return;
  }

  if (newMessage.trim().length < 15) {
    alert('Message must be at least 15 characters.');
    return;
  }

  item.title = newTitle.trim();
  item.message = newMessage.trim();

  saveFeedbacks(feedbacks);
  loadMyFeedback();
}

function deleteFeedback(id) {
  if (!confirm('Are you sure you want to delete this feedback?')) return;

  const feedbacks = getFeedbacks();
  const item = feedbacks.find(feedback => feedback.id === id);

  if (!item || item.status !== 'Pending') {
    alert('Only pending feedback can be deleted.');
    return;
  }

  const updatedFeedbacks = feedbacks.filter(feedback => feedback.id !== id);
  saveFeedbacks(updatedFeedbacks);
  loadMyFeedback();
}

function loadAdminDashboard() {
  const feedbacks = getFeedbacks();

  const total = feedbacks.length;
  const pending = feedbacks.filter(item => item.status === 'Pending').length;
  const reviewed = feedbacks.filter(item => item.status === 'Reviewed').length;
  const resolved = feedbacks.filter(item => item.status === 'Resolved').length;

  document.getElementById('adminTotal').innerText = total;
  document.getElementById('adminPending').innerText = pending;
  document.getElementById('adminReviewed').innerText = reviewed;
  document.getElementById('adminResolved').innerText = resolved;
}

function loadAdminFeedbacks(list = null) {
  const feedbacks = list || getFeedbacks();
  const container = document.getElementById('adminFeedbackList');
  container.innerHTML = '';

  if (feedbacks.length === 0) {
    container.innerHTML = '<div class="card"><p>No feedback available.</p></div>';
    return;
  }

  feedbacks.slice().reverse().forEach(item => {
    container.innerHTML += `
      <div class="card">
        <div class="card-top">
          <h3>${item.title}</h3>
          <span class="status-badge ${item.status.toLowerCase()}">${item.status}</span>
        </div>
        <p><strong>Student:</strong> ${item.studentName}</p>
        <p><strong>Email:</strong> ${item.userEmail}</p>
        <p><strong>Category:</strong> ${item.category}</p>
        <p><strong>Date:</strong> ${item.createdAt}</p>
        <p><strong>Message:</strong> ${item.message}</p>

        <label for="status-${item.id}">Update Status</label>
        <select id="status-${item.id}">
          <option value="Pending" ${item.status === 'Pending' ? 'selected' : ''}>Pending</option>
          <option value="Reviewed" ${item.status === 'Reviewed' ? 'selected' : ''}>Reviewed</option>
          <option value="Resolved" ${item.status === 'Resolved' ? 'selected' : ''}>Resolved</option>
        </select>

        <label for="response-${item.id}">Admin Response</label>
        <textarea id="response-${item.id}" placeholder="Write response...">${item.response || ''}</textarea>

        <div class="action-row">
          <button onclick="saveAdminUpdate(${item.id})">Save Update</button>
          <button class="danger-btn" onclick="deleteAdminFeedback(${item.id})">Delete</button>
        </div>
      </div>
    `;
  });
}

function saveAdminUpdate(id) {
  const feedbacks = getFeedbacks();
  const item = feedbacks.find(feedback => feedback.id === id);

  if (!item) return;

  item.status = document.getElementById(`status-${id}`).value;
  item.response = document.getElementById(`response-${id}`).value.trim();

  saveFeedbacks(feedbacks);
  alert('Feedback updated successfully.');
  loadAdminFeedbacks();
}

function deleteAdminFeedback(id) {
  if (!confirm('Delete this feedback?')) return;

  const updatedFeedbacks = getFeedbacks().filter(item => item.id !== id);
  saveFeedbacks(updatedFeedbacks);
  loadAdminFeedbacks();
}

function searchAdminFeedback() {
  const keyword = document.getElementById('searchInput').value.trim().toLowerCase();
  const status = document.getElementById('filterStatus').value;

  let feedbacks = getFeedbacks();

  feedbacks = feedbacks.filter(item => {
    const matchesKeyword =
      !keyword ||
      item.title.toLowerCase().includes(keyword) ||
      item.message.toLowerCase().includes(keyword) ||
      item.studentName.toLowerCase().includes(keyword) ||
      item.category.toLowerCase().includes(keyword);

    const matchesStatus = !status || item.status === status;

    return matchesKeyword && matchesStatus;
  });

  loadAdminFeedbacks(feedbacks);
}

seedDefaultData();
function loadWelcomeCard() {
  const user = getCurrentUser();
  const welcomeBox = document.getElementById('welcomeBox');

  if (!welcomeBox || !user) return;

  welcomeBox.innerHTML = `
    <div class="welcome-card">
      <h2>Welcome, ${user.name} 👋</h2>
      <p>Here’s your dashboard. You can track your feedback and updates easily.</p>
    </div>
  `;
}