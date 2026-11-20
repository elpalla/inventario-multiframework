/* Frontend: Bootstrap 5 + Fetch API (JavaScript puro). Consume la API REST de Slim. */
const API = window.API_URL;
const $ = (id) => document.getElementById(id);
const usd = new Intl.NumberFormat("en-US", { style: "currency", currency: "USD", maximumFractionDigits: 0 });
const usd2 = new Intl.NumberFormat("en-US", { style: "currency", currency: "USD" });

let productos = [];
let productoSel = null;
let debounce;

/* ---------- utilidades ---------- */
const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
const getToken = () => sessionStorage.getItem("jwt");

function toast(msg, tipo = "success") {
  const el = document.createElement("div");
  el.className = `alert alert-${tipo} shadow-sm py-2 mb-2`;
  el.textContent = msg;
  $("toasts").appendChild(el);
  setTimeout(() => el.remove(), 4500);
}

async function api(path, { method = "GET", body, auth = false } = {}) {
  const headers = { Accept: "application/json" };
  if (body) headers["Content-Type"] = "application/json";
  if (auth && getToken()) headers["Authorization"] = "Bearer " + getToken();
  let res;
  try {
    res = await fetch(API + path, { method, headers, body: body ? JSON.stringify(body) : undefined });
  } catch (e) {
    throw { status: 0, message: "No se pudo conectar con la API (¿está encendida en " + API + "?)" };
  }
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw { status: res.status, message: data.message || "Error " + res.status };
  return data;
}

/* ---------- sesión ---------- */
function pintarSesion() {
  const logged = !!getToken();
  $("btnLogin").classList.toggle("d-none", logged);
  $("btnLogout").classList.toggle("d-none", !logged);
  $("userLabel").classList.toggle("d-none", !logged);
  $("userLabel").textContent = logged ? "👤 " + (sessionStorage.getItem("jwtUser") || "") : "";
}

$("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  $("loginError").classList.add("d-none");
  try {
    const r = await api("/api/login", { method: "POST", body: { email: $("lEmail").value, password: $("lPass").value } });
    sessionStorage.setItem("jwt", r.data.token);
    sessionStorage.setItem("jwtUser", r.data.usuario.nombre);
    bootstrap.Modal.getOrCreateInstance($("loginModal")).hide();
    pintarSesion();
    toast("Sesión iniciada como " + r.data.usuario.nombre);
  } catch (err) {
    $("loginError").textContent = err.message;
    $("loginError").classList.remove("d-none");
  }
});

$("btnLogout").addEventListener("click", () => {
  sessionStorage.removeItem("jwt");
  sessionStorage.removeItem("jwtUser");
  pintarSesion();
  toast("Sesión cerrada", "secondary");
});

/* ---------- datos ---------- */
async function cargarResumen() {
  const { data } = await api("/api/reportes/resumen");
  $("kpiRef").textContent = data.referencias.toLocaleString();
  $("kpiUni").textContent = data.unidades.toLocaleString();
  $("kpiVal").textContent = usd.format(data.valor_inventario);
  $("kpiBajo").textContent = data.bajo_stock;
}

async function cargarFiltros() {
  const r = await api("/api/categorias");
  $("fCat").innerHTML = '<option value="">Todas</option>' + r.data.map((c) => `<option value="${c.id}">${esc(c.nombre)}</option>`).join("");
  $("fPlanta").innerHTML = '<option value="">Todas</option>' + r.plantas.map((p) => `<option>${esc(p)}</option>`).join("");
}

async function cargarProductos() {
  const p = new URLSearchParams();
  if ($("fQ").value.trim()) p.set("q", $("fQ").value.trim());
  if ($("fCat").value) p.set("categoria_id", $("fCat").value);
  if ($("fPlanta").value) p.set("planta", $("fPlanta").value);
  const r = await api("/api/productos" + (p.toString() ? "?" + p : ""));
  productos = r.data;
  $("contador").textContent = r.total;
  $("ultima").textContent = new Date().toLocaleTimeString();
  $("tbody").innerHTML = productos.length
    ? productos.map((x) => `
      <tr>
        <td><code>${esc(x.sku)}</code></td>
        <td><div class="fw-semibold">${esc(x.nombre)}</div><div class="small text-muted d-none d-sm-block">${esc(x.descripcion)}</div></td>
        <td class="d-none d-md-table-cell">${esc(x.categoria)}</td>
        <td class="d-none d-lg-table-cell">${esc(x.planta)}</td>
        <td class="text-end">${usd2.format(x.precio)}</td>
        <td class="text-end"><span class="badge ${x.bajo_stock ? "text-bg-danger" : "text-bg-success"}">${x.stock}</span></td>
        <td class="text-end"><button class="btn btn-sm btn-vr" data-id="${x.id}" ${x.stock < 1 ? "disabled" : ""}>Descontar</button></td>
      </tr>`).join("")
    : '<tr><td colspan="7" class="text-center py-4 text-muted">Sin resultados.</td></tr>';
}

async function refrescar() {
  try { await Promise.all([cargarResumen(), cargarProductos()]); }
  catch (err) { $("tbody").innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">${esc(err.message)}</td></tr>`; }
}

/* ---------- filtros en tiempo real ---------- */
$("fQ").addEventListener("input", () => { clearTimeout(debounce); debounce = setTimeout(cargarProductos, 300); });
$("fCat").addEventListener("change", cargarProductos);
$("fPlanta").addEventListener("change", cargarProductos);
$("btnLimpiar").addEventListener("click", () => { $("fQ").value = ""; $("fCat").value = ""; $("fPlanta").value = ""; cargarProductos(); });

/* ---------- descontar unidades ---------- */
$("tbody").addEventListener("click", (e) => {
  const btn = e.target.closest("button[data-id]");
  if (!btn) return;
  if (!getToken()) {
    toast("Inicia sesión para descontar inventario.", "warning");
    bootstrap.Modal.getOrCreateInstance($("loginModal")).show();
    return;
  }
  productoSel = productos.find((p) => p.id === Number(btn.dataset.id));
  $("dNombre").textContent = productoSel.nombre;
  $("dStock").textContent = productoSel.stock;
  $("dCant").value = 1;
  $("dCant").max = productoSel.stock;
  bootstrap.Modal.getOrCreateInstance($("descModal")).show();
});

$("descForm").addEventListener("submit", async (e) => {
  e.preventDefault();
  try {
    const r = await api("/api/productos/descontar", {
      method: "POST", auth: true,
      body: { producto_id: productoSel.id, cantidad: Number($("dCant").value) },
    });
    bootstrap.Modal.getOrCreateInstance($("descModal")).hide();
    toast(r.message);
    refrescar();                       // sin recargar la página
  } catch (err) {
    if (err.status === 401) {
      sessionStorage.removeItem("jwt"); pintarSesion();
      bootstrap.Modal.getOrCreateInstance($("descModal")).hide();
      toast("Tu sesión expiró. Inicia sesión de nuevo.", "warning");
      bootstrap.Modal.getOrCreateInstance($("loginModal")).show();
    } else {
      toast(err.message, "danger");
    }
  }
});

/* ---------- arranque ---------- */
pintarSesion();
cargarFiltros().then(refrescar).catch((e) => toast(e.message, "danger"));
setInterval(() => { if (!document.querySelector(".modal.show")) refrescar(); }, 10000);
