const state = {
  dashboard: null,
  product: null,
  orders: [],
  meta: { page: 1, last_page: 1, total: 0 },
  selectedOrder: null,
  searchTimer: null,
};

const statusLabels = { pending: 'بانتظار التأكيد', confirmed: 'مؤكد', shipped: 'قيد الشحن', delivered: 'تم التسليم', cancelled: 'ملغى', returned: 'مرتجع' };
const actionLabels = { order_created: 'تم إنشاء الطلب', order_updated: 'تم تحديث الطلب', dispatched_to_noest: 'تم إرسال الطلب إلى NOEST' };
const colorImages = { 'أسود': '/assets/charm-black.png', 'بني': '/assets/charm-brown.png', 'أخضر': '/assets/charm-green.png', 'بيج': '/assets/charm-beige.png' };

const loginScreen = document.querySelector('#login-screen');
const adminShell = document.querySelector('#admin-shell');
const toast = document.querySelector('#toast');

function money(value) { return `${new Intl.NumberFormat('en-DZ').format(Number(value || 0))} دج`; }
function dateTime(value) { return value ? new Intl.DateTimeFormat('ar-DZ', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '—'; }
function escapeHtml(value) { return String(value ?? '').replace(/[&<>'"]/g, (char) => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#39;', '"':'&quot;' })[char]); }

async function api(url, options = {}) {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const response = await fetch(url, {
    ...options,
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json', ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}), ...(options.headers || {}) },
  });
  const payload = await response.json().catch(() => ({}));
  if (response.status === 401 && !url.endsWith('/login')) showLogin(false);
  if (!response.ok) {
    const validationMessage = Object.values(payload.errors || {})[0]?.[0];
    const error = new Error(validationMessage || payload.message || 'تعذّر إكمال العملية.');
    error.status = response.status;
    error.payload = payload;
    throw error;
  }
  return payload;
}

function showToast(message, isError = false) {
  toast.textContent = message;
  toast.classList.toggle('error', isError);
  toast.classList.add('show');
  clearTimeout(showToast.timer);
  showToast.timer = setTimeout(() => toast.classList.remove('show'), 2800);
}

function showLogin(notify = false) {
  adminShell.hidden = true;
  loginScreen.hidden = false;
  if (notify) showToast('تم تسجيل الخروج.');
}

async function logout(notify = true) {
  try {
    const payload = await api('/api/admin/logout', { method: 'POST' });
    if (payload.csrf_token) document.querySelector('meta[name="csrf-token"]').content = payload.csrf_token;
  } catch (error) { if (error.status !== 401) showToast(error.message, true); }
  showLogin(notify);
}

async function login(event) {
  event.preventDefault();
  const form = event.currentTarget;
  const button = form.querySelector('button');
  const errorNode = document.querySelector('#login-error');
  errorNode.textContent = '';
  button.disabled = true;
  button.firstChild.textContent = 'جاري الدخول… ';
  try {
    await api('/api/admin/login', { method: 'POST', body: JSON.stringify(Object.fromEntries(new FormData(form))) });
    await enterDashboard();
  } catch (error) {
    errorNode.textContent = error.message;
  } finally {
    button.disabled = false;
    button.firstChild.textContent = 'دخول لوحة التحكم ';
  }
}

async function enterDashboard() {
  try {
    await loadDashboard();
    loginScreen.hidden = true;
    adminShell.hidden = false;
  } catch (error) {
    if (error.status !== 401) document.querySelector('#login-error').textContent = error.message;
    throw error;
  }
}

async function loadDashboard() {
  const stats = await api('/api/admin/dashboard');
  state.dashboard = stats;
  document.querySelector('#stat-revenue').textContent = money(stats.total_revenue);
  document.querySelector('#stat-orders').textContent = stats.total_orders;
  document.querySelector('#stat-today').textContent = stats.today_orders;
  document.querySelector('#stat-pending').textContent = stats.pending_orders;
  document.querySelector('#stat-stock').textContent = stats.total_stock;
  document.querySelector('#pending-badge').textContent = stats.pending_orders;
  const noestDot = document.querySelector('#noest-dot');
  noestDot.classList.toggle('online', stats.noest_configured);
  document.querySelector('#noest-status').textContent = stats.noest_configured ? 'متصل وجاهز للإرسال' : 'بانتظار مفاتيح API';
  renderRecent(stats.recent_orders);
  renderStatusChart(stats);
}

function renderRecent(orders) {
  const container = document.querySelector('#recent-orders');
  if (!orders.length) {
    container.innerHTML = '<p class="empty-row">لا توجد طلبات بعد.</p>';
    return;
  }
  container.innerHTML = orders.map((order) => `
    <article class="recent-order" data-order-id="${order.id}">
      <span class="order-avatar">${escapeHtml(order.customer_name?.trim()?.[0] || 'ط')}</span>
      <p><strong>${escapeHtml(order.customer_name)}</strong><span>${escapeHtml(order.order_number)} · ${dateTime(order.created_at)}</span></p>
      <strong>${money(order.total)}</strong>
    </article>`).join('');
  container.querySelectorAll('[data-order-id]').forEach((row) => row.addEventListener('click', () => openOrder(row.dataset.orderId)));
}

function renderStatusChart(stats) {
  const values = [
    ['بانتظار التأكيد', stats.pending_orders, '#ae7224'],
    ['قيد الشحن', stats.shipped_orders, '#704d92'],
    ['تم التسليم', stats.delivered_orders, '#43704e'],
    ['ملغى', stats.cancelled_orders, '#b43d45'],
  ];
  const max = Math.max(1, ...values.map((item) => item[1]));
  document.querySelector('#status-chart').innerHTML = values.map(([label, value, color]) => `<div class="status-row"><span>${label}</span><i style="--value:${Math.round(value / max * 100)}%;--bar:${color}"></i><b>${value}</b></div>`).join('');
}

function switchView(view) {
  document.querySelectorAll('.nav-item').forEach((button) => button.classList.toggle('is-active', button.dataset.view === view));
  document.querySelectorAll('[data-view-panel]').forEach((panel) => panel.classList.toggle('is-active', panel.dataset.viewPanel === view));
  document.querySelector('#view-title').textContent = { overview: 'نظرة عامة', orders: 'إدارة الطلبات', product: 'المنتج والمخزون' }[view];
  window.scrollTo({ top: 0, behavior: 'smooth' });
  document.querySelector('.sidebar').classList.remove('is-open');
  if (view === 'orders') loadOrders();
  if (view === 'product') loadProduct();
}

async function loadOrders(page = state.meta.page || 1) {
  const search = document.querySelector('#order-search').value.trim();
  const status = document.querySelector('#status-filter').value;
  const body = document.querySelector('#orders-body');
  body.innerHTML = '<tr><td colspan="8" class="empty-row">جاري تحميل الطلبات…</td></tr>';
  try {
    const payload = await api(`/api/admin/orders?page=${page}&status=${encodeURIComponent(status)}&search=${encodeURIComponent(search)}`);
    state.orders = payload.data;
    state.meta = payload.meta;
    renderOrders();
  } catch (error) {
    body.innerHTML = `<tr><td colspan="8" class="empty-row">${escapeHtml(error.message)}</td></tr>`;
  }
}

function renderOrders() {
  const body = document.querySelector('#orders-body');
  document.querySelector('#orders-count').textContent = `${state.meta.total} طلب`;
  if (!state.orders.length) {
    body.innerHTML = '<tr><td colspan="8" class="empty-row">لا توجد طلبات مطابقة.</td></tr>';
  } else {
    body.innerHTML = state.orders.map((order) => `
      <tr data-order-id="${order.id}">
        <td><strong>${escapeHtml(order.order_number)}</strong><br><small>${dateTime(order.created_at)}</small></td>
        <td><div class="customer-cell"><strong>${escapeHtml(order.customer_name)}</strong><span>${escapeHtml(order.phone)}</span></div></td>
        <td>${escapeHtml(order.wilaya_name)}<br><small>${escapeHtml(order.commune)}</small></td>
        <td><div class="product-cell"><img src="${colorImages[order.color] || '/assets/charm-black.png'}" alt=""><div>Charm<span>${escapeHtml(order.color)} × ${order.quantity}</span></div></div></td>
        <td><strong>${money(order.total)}</strong><br><small>شحن ${money(order.shipping_fee)}</small></td>
        <td><span class="status-badge status-${order.status}">${statusLabels[order.status] || escapeHtml(order.status)}</span></td>
        <td><span class="noest-chip ${order.noest?.dispatched ? 'sent' : ''}">${order.noest?.tracking ? escapeHtml(order.noest.tracking) : 'لم يُرسل'}</span></td>
        <td><button class="row-action" aria-label="فتح الطلب">•••</button></td>
      </tr>`).join('');
    body.querySelectorAll('[data-order-id]').forEach((row) => row.addEventListener('click', () => openOrder(row.dataset.orderId)));
  }
  const pagination = document.querySelector('#pagination');
  pagination.innerHTML = Array.from({ length: state.meta.last_page }, (_, index) => index + 1).map((page) => `<button class="${page === state.meta.page ? 'is-active' : ''}" data-page="${page}">${page}</button>`).join('');
  pagination.querySelectorAll('button').forEach((button) => button.addEventListener('click', () => loadOrders(Number(button.dataset.page))));
}

async function openOrder(id) {
  try {
    const payload = await api(`/api/admin/orders/${id}`);
    const order = payload.data;
    state.selectedOrder = order;
    const drawer = document.querySelector('#order-drawer');
    const form = document.querySelector('#order-edit-form');
    document.querySelector('#drawer-title').textContent = order.order_number;
    form.elements.id.value = order.id;
    ['status', 'payment_status', 'customer_name', 'phone', 'address', 'wilaya_name', 'commune', 'notes'].forEach((name) => { if (form.elements[name]) form.elements[name].value = order[name] || ''; });
    document.querySelector('#drawer-product-image').src = colorImages[order.color] || '/assets/charm-black.png';
    document.querySelector('#drawer-variant').textContent = `${order.color} × ${order.quantity}`;
    document.querySelector('#drawer-prices').textContent = `${money(order.subtotal)} + توصيل ${money(order.shipping_fee)} = ${money(order.total)}`;
    const noestText = document.querySelector('#drawer-noest-text');
    const dispatch = document.querySelector('#dispatch-button');
    noestText.textContent = order.noest?.tracking ? `رقم التتبع: ${order.noest.tracking}` : (state.dashboard?.noest_configured ? 'جاهز للإرسال' : 'مفاتيح API غير مضافة');
    dispatch.disabled = !state.dashboard?.noest_configured || order.noest?.dispatched;
    dispatch.textContent = order.noest?.dispatched ? 'تم الإرسال' : 'إرسال إلى NOEST';
    document.querySelector('#drawer-error').textContent = '';
    renderHistory(order.history || []);
    drawer.hidden = false;
    document.body.style.overflow = 'hidden';
  } catch (error) { showToast(error.message, true); }
}

function renderHistory(history) {
  const container = document.querySelector('#order-history');
  container.innerHTML = history.slice().reverse().map((entry) => `<div class="history-entry"><span>${actionLabels[entry.action] || escapeHtml(entry.action)}</span><small>${dateTime(entry.at)}${entry.tracking ? ` · ${escapeHtml(entry.tracking)}` : ''}</small></div>`).join('') || '<p class="empty-row">لا يوجد سجل بعد.</p>';
}

function closeDrawer() {
  document.querySelector('#order-drawer').hidden = true;
  document.body.style.overflow = '';
  state.selectedOrder = null;
}

async function saveOrder(event) {
  event.preventDefault();
  const form = event.currentTarget;
  const data = Object.fromEntries(new FormData(form));
  const id = data.id;
  delete data.id;
  document.querySelector('#drawer-error').textContent = '';
  try {
    const payload = await api(`/api/admin/orders/${id}`, { method: 'PATCH', body: JSON.stringify(data) });
    state.selectedOrder = payload.data;
    showToast('تم حفظ التعديلات.');
    closeDrawer();
    await Promise.all([loadDashboard(), loadOrders(state.meta.page)]);
  } catch (error) { document.querySelector('#drawer-error').textContent = error.message; }
}

async function dispatchSelectedOrder() {
  if (!state.selectedOrder) return;
  const button = document.querySelector('#dispatch-button');
  button.disabled = true;
  button.textContent = 'جاري الإرسال…';
  try {
    const payload = await api(`/api/admin/orders/${state.selectedOrder.id}/dispatch`, { method: 'POST' });
    showToast(payload.message);
    closeDrawer();
    await Promise.all([loadDashboard(), loadOrders(state.meta.page)]);
  } catch (error) {
    document.querySelector('#drawer-error').textContent = error.message;
    button.disabled = false;
    button.textContent = 'إرسال إلى NOEST';
  }
}

async function deleteSelectedOrder() {
  if (!state.selectedOrder || !window.confirm(`حذف الطلب ${state.selectedOrder.order_number} نهائيًا؟`)) return;
  try {
    const payload = await api(`/api/admin/orders/${state.selectedOrder.id}`, { method: 'DELETE' });
    showToast(payload.message);
    closeDrawer();
    await Promise.all([loadDashboard(), loadOrders(1)]);
  } catch (error) { document.querySelector('#drawer-error').textContent = error.message; }
}

async function loadProduct() {
  try {
    const payload = await api('/api/admin/product');
    state.product = payload.data;
    const form = document.querySelector('#product-form');
    form.elements.price.value = state.product.price;
    form.elements.compare_at_price.value = state.product.compare_at_price || '';
    form.elements.active.checked = state.product.active;
    form.elements.track_inventory.checked = state.product.track_inventory;
    document.querySelector('#product-availability').textContent = state.product.active ? 'متاح للبيع' : 'متوقف مؤقتًا';
    document.querySelector('#product-updated').textContent = state.product.updated_at ? dateTime(state.product.updated_at) : 'لم يُعدّل بعد';
    renderInventory();
  } catch (error) { showToast(error.message, true); }
}

function renderInventory() {
  const container = document.querySelector('#inventory-list');
  container.innerHTML = state.product.variants.map((variant) => `
    <div class="inventory-row" data-key="${variant.key}">
      <img src="${variant.image}" alt="">
      <p><strong>${escapeHtml(variant.name)}</strong><span>${variant.key.toUpperCase()}</span></p>
      <input type="number" min="0" max="100000" value="${variant.stock}" aria-label="مخزون ${escapeHtml(variant.name)}">
      <label class="switch-label"><input type="checkbox" ${variant.active ? 'checked' : ''}><span></span>متاح</label>
    </div>`).join('');
  updateStockTotal();
  container.querySelectorAll('input').forEach((input) => input.addEventListener('input', updateStockTotal));
}

function updateStockTotal() {
  const total = [...document.querySelectorAll('.inventory-row input[type="number"]')].reduce((sum, input) => sum + (Number(input.value) || 0), 0);
  document.querySelector('#product-total-stock').textContent = total;
}

async function saveProduct(event) {
  event.preventDefault();
  const form = event.currentTarget;
  const variants = [...document.querySelectorAll('.inventory-row')].map((row) => ({ key: row.dataset.key, stock: Number(row.querySelector('input[type="number"]').value), active: row.querySelector('input[type="checkbox"]').checked }));
  const data = { price: Number(form.elements.price.value), compare_at_price: Number(form.elements.compare_at_price.value || 0), active: form.elements.active.checked, track_inventory: form.elements.track_inventory.checked, variants };
  const button = form.querySelector('button[type="submit"]');
  button.disabled = true;
  try {
    const payload = await api('/api/admin/product', { method: 'PATCH', body: JSON.stringify(data) });
    state.product = payload.data;
    showToast(payload.message);
    await Promise.all([loadProduct(), loadDashboard()]);
  } catch (error) { showToast(error.message, true); }
  finally { button.disabled = false; }
}

async function exportOrders() {
  try {
    const response = await fetch('/api/admin/orders/export', { credentials: 'same-origin', headers: { Accept: 'text/csv' } });
    if (!response.ok) throw new Error('تعذّر تصدير الطلبات.');
    const blob = await response.blob();
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `charm-orders-${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
  } catch (error) { showToast(error.message, true); }
}

document.querySelector('#today-label').textContent = new Intl.DateTimeFormat('ar-DZ', { weekday:'long', year:'numeric', month:'long', day:'numeric' }).format(new Date());
document.querySelector('#login-form').addEventListener('submit', login);
document.querySelector('#logout-button').addEventListener('click', () => logout());
document.querySelector('#menu-button').addEventListener('click', () => document.querySelector('.sidebar').classList.toggle('is-open'));
document.querySelectorAll('.nav-item').forEach((button) => button.addEventListener('click', () => switchView(button.dataset.view)));
document.querySelectorAll('[data-go-orders]').forEach((button) => button.addEventListener('click', () => switchView('orders')));
document.querySelector('#refresh-button').addEventListener('click', async () => { await loadDashboard(); const active = document.querySelector('.nav-item.is-active')?.dataset.view; if (active === 'orders') await loadOrders(); if (active === 'product') await loadProduct(); showToast('تم تحديث البيانات.'); });
document.querySelector('#order-search').addEventListener('input', () => { clearTimeout(state.searchTimer); state.searchTimer = setTimeout(() => loadOrders(1), 350); });
document.querySelector('#status-filter').addEventListener('change', () => loadOrders(1));
document.querySelector('#export-button').addEventListener('click', exportOrders);
document.querySelectorAll('[data-close-drawer]').forEach((button) => button.addEventListener('click', closeDrawer));
document.querySelector('#order-edit-form').addEventListener('submit', saveOrder);
document.querySelector('#dispatch-button').addEventListener('click', dispatchSelectedOrder);
document.querySelector('#delete-order').addEventListener('click', deleteSelectedOrder);
document.querySelector('#product-form').addEventListener('submit', saveProduct);
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeDrawer(); });

enterDashboard().catch((error) => { if (error.status !== 401) showToast(error.message, true); showLogin(false); });
