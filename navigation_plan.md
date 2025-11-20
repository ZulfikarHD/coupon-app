Got it — I’ve read your scrum file and now have a **full picture of the app**.
This changes the navigation discussion a LOT, because this system is:

* An **internal tool**
* Used mostly by **staff**
* Heavy on **forms**, **tables**, **scan workflow**, and **filters**
* Built with **Inertia + Vue**
* Has only **4 main menus**:

  * Dashboard
  * Kupon
  * Scan Kupon
  * Laporan

So let’s talk about the UX like a modern SaaS admin panel expert.

---

# ✅ First: What *is* the ideal navigation style for *this* app?

Breadcrumbs are NOT your primary navigation in modern apps.
Breadcrumbs are **secondary** navigation used only to show location inside hierarchy:

example:
Kupon → Detail → History

This app barely has deep layers.
So breadcrumbs will NOT solve mobile UX.

---

# ✔ The BEST navigation pattern for THIS system (based on the scrum)

## 🔥 **1. Permanent Sidebar (desktop)**

Modern admin UI pattern:

* Collapsible sidebar
* Icons + labels
* Highlight active menu

This matches your 4 menus perfectly.

Example:
Superhuman, Linear, Notion, Figma Admin, Stripe Dashboard.

---

## 🔥 **2. Hamburger + Bottom Sheet (mobile)**

This is the modern approach for enterprise web apps:

### Why?

* Works well for short menu list (you have only 4!)
* Easy for thumb navigation
* Consistent across Android & iOS

### Flow:

On mobile:

* Top-left: hamburger icon
* Tapping opens a **full-width bottom sheet** (not a side drawer)
* User simply taps the menu

This is now more modern than slide-out sidebars because:

* Side drawers are awkward for thumb (hard to reach)
* Bottom sheets are closer to native app behavior

Examples:

* Shopify Admin mobile
* Google Analytics
* Airtable mobile
* Linear mobile web

---

# ✔ 3. Bottom Navigation Bar (Optional but Highly Recommended)

Bottom Nav is perfect if your app has:

* Few menus
* Repetitive workflows
* High mobile usage
* One “core” action (Scan)

Your app fits ALL.

Your core workflows:

* Staff open the app → **Scan Kupon**
* Occasionally go to Kupon list
* Occasionally go to Dashboard or Reports

Bottom Nav with FAB Scan button will skyrocket usability.

### Recommended bottom nav:

```
📊 Dashboard   🎟️ Kupon   📷 Scan (Center FAB)   📈 Laporan
```

FAB scan button = always reachable = blazing fast workflow.

This is a **huge UX win**.

---

# ✔ 4. Breadcrumbs (Secondary, but optional)

Breadcrumbs only matter on:

* Coupon detail
* Reports detail
* Advanced filter results

Since your hierarchy is shallow, breadcrumbs are "nice-to-have" but not necessary for navigation.

Do NOT rely on breadcrumbs for main navigation in mobile.
Use them only for “context”, not “movement”.

---

# 🧭 Which pattern is the most modern in 2025?

### Most modern mobile navigation today is:

**Hybrid navigation** =

* Bottom nav for core sections
* Hamburger or bottom sheet for secondary options
* FAB for main action
* Optional breadcrumbs inside pages

This fits perfectly with your 4-menu layout.

---

# 📱 Recommended Mobile Navigation Design (FINAL)

Let me give you a final layout:

---

### **Top bar**

* Left: Hamburger
* Center: Page title
* Right: User avatar

---

### **Bottom nav**

* Dashboard
* Kupon
* Scan (FAB, big)
* Laporan

**FAB sticks out** and is green/orange (as defined in your scrum).

---

### **Hamburger content (Bottom Sheet Style)**

```
📊 Dashboard
🎟️ Kupon
    • Semua Kupon
    • Buat Baru
📷 Scan Kupon
📈 Laporan
⚙️ Pengaturan
🚪 Logout
```

---

### **Breadcrumbs (Optional)**

Shown only on:

* Kupon → Detail
* Kupon → Edit
* Laporan → Detail

Breadcrumb:
`Kupon / CODE-1234 / Riwayat`

Good for orientation, not navigation.

---

# 🧠 Why this is the best UX for your app?

Because your staff:

* Use mobile phones for scanning
* Need 1-hand navigation
* Need the scan button always accessible
* Perform repetitive actions
* Need fast access (no buried menus)

This gives:

* Maximum speed
* Minimum taps
* Clean, modern interface
* No cognitive load
* Perfect for both Android and iPhone users

---
