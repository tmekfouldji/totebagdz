const state = {
  product: null,
  selectedColor: 'أسود',
  quantity: 1,
  shippingFee: null,
  submitting: false,
};

const colorImages = {
  'أسود': '/assets/charm-black.png',
  'بني': '/assets/charm-brown.png',
  'أخضر': '/assets/charm-green.png',
  'بيج': '/assets/charm-beige.png',
};

const form = document.querySelector('#order-form');
const wilayaSelect = document.querySelector('#wilaya-select');
const communeSelect = document.querySelector('#commune-select');
const quantitySelect = document.querySelector('#quantity-select');
const submitButton = document.querySelector('#submit-order');
const formStatus = document.querySelector('#form-status');
const mainImage = document.querySelector('#main-product-image');
const orderImage = document.querySelector('#order-product-image');

function formatDzd(value) {
  return `${new Intl.NumberFormat('en-DZ', { maximumFractionDigits: 0 }).format(Number(value || 0))} دج`;
}

async function api(url, options = {}) {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const response = await fetch(url, {
    ...options,
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json', ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}), ...(options.headers || {}) },
  });
  const payload = await response.json().catch(() => ({}));
  if (!response.ok) {
    const validationMessage = Object.values(payload.errors || {})[0]?.[0];
    const error = new Error(validationMessage || payload.message || 'تعذّر الاتصال بالخادم.');
    error.status = response.status;
    error.payload = payload;
    throw error;
  }
  return payload;
}

function updateTotals() {
  const price = Number(state.product?.price || 1900);
  const subtotal = price * state.quantity;
  document.querySelector('#subtotal-price').textContent = formatDzd(subtotal);
  document.querySelector('#shipping-price').textContent = state.shippingFee === null ? 'اختاري الولاية' : formatDzd(state.shippingFee);
  document.querySelector('#total-price').textContent = state.shippingFee === null ? '—' : formatDzd(subtotal + state.shippingFee);
  submitButton.disabled = state.shippingFee === null || state.submitting || state.product?.active === false;
}

function setColor(color, image = colorImages[color], { scroll = false } = {}) {
  const variant = state.product?.variants?.find((item) => item.name === color);
  if (variant && (!variant.active || (state.product.track_inventory && variant.stock < 1))) return;
  state.selectedColor = color;
  orderImage.src = image;
  document.querySelector('#summary-color').textContent = color;
  const radio = form.querySelector(`input[name="color"][value="${color}"]`);
  if (radio) radio.checked = true;

  document.querySelectorAll('.color-card').forEach((card) => {
    const selected = card.dataset.color === color;
    card.classList.toggle('is-selected', selected);
    card.setAttribute('aria-pressed', String(selected));
  });

  if (scroll) document.querySelector('#order').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function renderProduct(product) {
  state.product = product;
  document.querySelectorAll('[data-product-price]').forEach((node) => { node.textContent = formatDzd(product.price); });
  document.querySelectorAll('.color-card').forEach((card) => {
    const variant = product.variants.find((item) => item.name === card.dataset.color);
    const unavailable = !variant?.active || (product.track_inventory && variant.stock < 1);
    card.disabled = unavailable;
    card.classList.toggle('is-unavailable', unavailable);
    if (unavailable) card.setAttribute('aria-label', `${card.dataset.color} — غير متوفر`);
  });
  form.querySelectorAll('input[name="color"]').forEach((radio) => {
    const variant = product.variants.find((item) => item.name === radio.value);
    const unavailable = !variant?.active || (product.track_inventory && variant.stock < 1);
    radio.disabled = unavailable;
    radio.closest('label')?.classList.toggle('is-unavailable', unavailable);
  });

  const selected = product.variants.find((item) => item.name === state.selectedColor);
  if (!selected?.active || (product.track_inventory && selected.stock < 1)) {
    const available = product.variants.find((item) => item.active && (!product.track_inventory || item.stock > 0));
    if (available) setColor(available.name, available.image);
  }
  if (!product.active) {
    formStatus.textContent = 'المنتج غير متاح للطلب حاليًا.';
    submitButton.disabled = true;
  }
  updateTotals();
}

async function loadProduct() {
  try {
    const payload = await api('/api/product');
    renderProduct(payload.data);
  } catch (error) {
    formStatus.textContent = error.message;
    renderProduct({ price: 1900, active: true, track_inventory: false, variants: [] });
  }
}

async function loadWilayas() {
  try {
    const payload = await api('/api/shipping/wilayas');
    wilayaSelect.innerHTML = '<option value="">اختاري الولاية</option>' + payload.data
      .map((wilaya) => `<option value="${wilaya.id}">${String(wilaya.id).padStart(2, '0')} — ${wilaya.name_ar || wilaya.name}</option>`)
      .join('');
  } catch (error) {
    wilayaSelect.innerHTML = '<option value="">تعذّر تحميل الولايات</option>';
    formStatus.textContent = error.message;
  }
}

async function handleWilayaChange() {
  const wilayaId = wilayaSelect.value;
  state.shippingFee = null;
  updateTotals();
  clearFieldError('wilaya_id');
  communeSelect.disabled = true;
  communeSelect.innerHTML = '<option value="">جاري تحميل البلديات…</option>';
  if (!wilayaId) {
    communeSelect.innerHTML = '<option value="">اختاري الولاية أولًا</option>';
    return;
  }

  try {
    const [communesPayload, feePayload] = await Promise.all([
      api(`/api/shipping/communes?wilaya_id=${encodeURIComponent(wilayaId)}`),
      api(`/api/shipping/fees?wilaya_id=${encodeURIComponent(wilayaId)}`),
    ]);
    communeSelect.innerHTML = '<option value="">اختاري البلدية</option>' + communesPayload.data
      .map((commune) => `<option value="${escapeHtml(commune.name)}">${escapeHtml(commune.name_ar || commune.name)}${commune.name_ar ? ` — ${escapeHtml(commune.name)}` : ''}</option>`)
      .join('');
    communeSelect.disabled = false;
    state.shippingFee = Number(feePayload.data.home_fee);
    formStatus.textContent = feePayload.source === 'noest' ? '' : 'تم احتساب الشحن وفق التعريفة المحلية الاحتياطية.';
    updateTotals();
  } catch (error) {
    communeSelect.innerHTML = '<option value="">تعذّر تحميل البلديات</option>';
    formStatus.textContent = error.message;
  }
}

function escapeHtml(value) {
  return String(value).replace(/[&<>'"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[char]);
}

function clearErrors() {
  form.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
  form.querySelectorAll('.field-error').forEach((field) => { field.textContent = ''; });
}

function clearFieldError(name) {
  form.querySelector(`[name="${name}"]`)?.removeAttribute('aria-invalid');
  const error = form.querySelector(`[data-error-for="${name}"]`);
  if (error) error.textContent = '';
}

function showErrors(errors = {}) {
  Object.entries(errors).forEach(([name, message]) => {
    const field = form.querySelector(`[name="${name}"]`);
    if (field) field.setAttribute('aria-invalid', 'true');
    const error = form.querySelector(`[data-error-for="${name}"]`);
    if (error) error.textContent = Array.isArray(message) ? message[0] : message;
  });
  form.querySelector('[aria-invalid="true"]')?.focus();
}

async function submitOrder(event) {
  event.preventDefault();
  clearErrors();
  formStatus.textContent = '';
  if (state.shippingFee === null) {
    showErrors({ wilaya_id: 'اختاري الولاية لحساب التوصيل.' });
    return;
  }

  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());
  data.quantity = Number(data.quantity);
  data.wilaya_id = Number(data.wilaya_id);
  state.submitting = true;
  submitButton.querySelector('span').textContent = 'جاري تسجيل الطلب…';
  updateTotals();

  try {
    const payload = await api('/api/orders', { method: 'POST', body: JSON.stringify(data) });
    document.querySelector('#success-order-number').textContent = payload.order.order_number;
    document.querySelector('#success-total').textContent = formatDzd(payload.order.total);
    openModal();
    form.reset();
    state.selectedColor = 'أسود';
    state.quantity = 1;
    state.shippingFee = null;
    communeSelect.disabled = true;
    communeSelect.innerHTML = '<option value="">اختاري الولاية أولًا</option>';
    setColor('أسود');
    await loadProduct();
  } catch (error) {
    formStatus.textContent = error.message;
    if (error.payload?.errors) showErrors(error.payload.errors);
  } finally {
    state.submitting = false;
    submitButton.querySelector('span').textContent = 'تأكيد الطلب';
    updateTotals();
  }
}

function openModal() {
  const modal = document.querySelector('#success-modal');
  modal.hidden = false;
  document.body.style.overflow = 'hidden';
  modal.querySelector('button')?.focus();
}

function closeModal() {
  document.querySelector('#success-modal').hidden = true;
  document.body.style.overflow = '';
}

document.querySelectorAll('.thumb').forEach((button) => {
  button.addEventListener('click', () => {
    mainImage.style.opacity = '0';
    window.setTimeout(() => {
      mainImage.src = button.dataset.image;
      mainImage.alt = button.getAttribute('aria-label');
      document.querySelector('.image-caption').textContent = button.dataset.caption;
      mainImage.style.opacity = '1';
    }, 180);
    document.querySelectorAll('.thumb').forEach((thumb) => {
      const active = thumb === button;
      thumb.classList.toggle('is-active', active);
      thumb.setAttribute('aria-pressed', String(active));
    });
  });
});

document.querySelectorAll('.color-card').forEach((card) => {
  card.addEventListener('click', () => setColor(card.dataset.color, card.dataset.image, { scroll: true }));
});

form.querySelectorAll('input[name="color"]').forEach((radio) => {
  radio.addEventListener('change', () => setColor(radio.value));
});

form.querySelectorAll('input, select').forEach((field) => {
  field.addEventListener('input', () => clearFieldError(field.name));
});

wilayaSelect.addEventListener('change', handleWilayaChange);
communeSelect.addEventListener('change', () => clearFieldError('commune'));
quantitySelect.addEventListener('change', () => {
  state.quantity = Number(quantitySelect.value);
  updateTotals();
});
form.addEventListener('submit', submitOrder);

document.querySelectorAll('[data-close-modal]').forEach((element) => element.addEventListener('click', closeModal));
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeModal(); });
document.querySelector('#year').textContent = new Date().getFullYear();

const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('is-visible');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach((element) => revealObserver.observe(element));

const mobileBar = document.querySelector('#mobile-buy-bar');
const orderObserver = new IntersectionObserver(([entry]) => mobileBar.classList.toggle('is-hidden', entry.isIntersecting), { threshold: 0.08 });
orderObserver.observe(document.querySelector('#order'));

await Promise.all([loadProduct(), loadWilayas()]);
