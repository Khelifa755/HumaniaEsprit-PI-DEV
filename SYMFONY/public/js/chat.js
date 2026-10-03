/**
 * LinkedIn-Style Chat Widget - JavaScript Controller
 * Handles all chat interactions and API calls
 */

class ChatWidget {
  constructor() {
    this.currentTab = "private";
    this.currentUserId = null;
    this.openConversations = new Map();
    this.messageCache = new Map();
    this.listFilter = "all";
    this.privateUsersCache = null;
    this.unreadPollTimer = null;
    this.colors = [
      "#3D7EE8",
      "#2DAA63",
      "#F5A623",
      "#E8392A",
      "#9B59B6",
      "#E67E22",
    ];

    this.init();
  }

  init() {
    const toggleBtn = document.getElementById("chat-toggle-btn");
    if (!toggleBtn) {
      console.log("Chat widget not found in this page.");
      return;
    }

    this.currentUserId = parseInt(document.body.dataset.userId, 10) || 0;

    document
      .getElementById("chat-toggle-btn")
      .addEventListener("click", () => this.toggleMainPanel());
    document
      .getElementById("chat-minimize-btn")
      .addEventListener("click", () => this.minimizeMainPanel());
    document
      .getElementById("chat-compose-btn")
      .addEventListener("click", () => this.openComposeDialog());

    document.querySelectorAll(".chat-filter-chip").forEach((chip) => {
      chip.addEventListener("click", () => {
        document
          .querySelectorAll(".chat-filter-chip")
          .forEach((c) => c.classList.remove("active"));
        chip.classList.add("active");
        this.listFilter = chip.dataset.filter || "all";
        this.applyPrivateListFilters();
      });
    });

    const composeOverlay = document.getElementById("chat-compose-overlay");
    document
      .getElementById("chat-compose-close")
      .addEventListener("click", () => this.closeComposeModal());
    composeOverlay.addEventListener("click", (e) => {
      if (e.target === composeOverlay) {
        this.closeComposeModal();
      }
    });
    document
      .getElementById("chat-compose-search")
      .addEventListener("input", (e) => this.filterComposeList(e.target.value));

    document.querySelectorAll(".chat-tab").forEach((tab) => {
      tab.addEventListener("click", (e) =>
        this.switchTab(e.target.dataset.tab),
      );
    });

    document
      .getElementById("chat-search-input")
      .addEventListener("input", (e) =>
        this.filterConversations(e.target.value),
      );

    document.addEventListener("keydown", (e) => {
      if (e.key !== "Escape") {
        return;
      }
      const compose = document.getElementById("chat-compose-overlay");
      if (compose && compose.classList.contains("is-open")) {
        e.preventDefault();
        this.closeComposeModal();
        return;
      }
      const panel = document.getElementById("chat-main-panel");
      if (panel && !panel.classList.contains("minimized")) {
        e.preventDefault();
        this.minimizeMainPanel();
        return;
      }
      if (this.openConversations.size > 0) {
        const keys = [...this.openConversations.keys()];
        const lastKey = keys[keys.length - 1];
        e.preventDefault();
        this.closeConversation(lastKey);
      }
    });

    document.addEventListener("visibilitychange", () => {
      this.scheduleUnreadPoll();
    });

    this.updateFilterRowVisibility();
    this.refreshUnreadBadgesOnce();
    this.scheduleUnreadPoll();
    this.attachMobileSheetGestures();
  }

  updateFilterRowVisibility() {
    const row = document.getElementById("chat-filter-row");
    if (row) {
      row.hidden = this.currentTab !== "private";
    }
  }

  showToast(message) {
    const host = document.getElementById("chat-toast-host");
    if (!host) {
      return;
    }
    const t = document.createElement("div");
    t.className = "chat-toast";
    t.textContent = message;
    host.appendChild(t);
    setTimeout(() => t.remove(), 4500);
  }

  toggleMainPanel() {
    const panel = document.getElementById("chat-main-panel");
    panel.classList.toggle("minimized");
    document.getElementById("chat-toggle-btn").classList.toggle("active");

    if (!panel.classList.contains("minimized")) {
      this.loadConversations();
    }
  }

  /**
   * Ouvre le panneau messagerie (header ou raccourci) sans le refermer s'il est déjà ouvert.
   */
  openMessenger() {
    const panel = document.getElementById("chat-main-panel");
    const toggleBtn = document.getElementById("chat-toggle-btn");
    if (!panel || !toggleBtn) {
      return;
    }
    if (panel.classList.contains("minimized")) {
      panel.classList.remove("minimized");
      toggleBtn.classList.add("active");
      this.loadConversations();
    }
    const search = document.getElementById("chat-search-input");
    if (search) {
      requestAnimationFrame(() => search.focus());
    }
  }

  updateUnreadBadges(count) {
    const n = Math.max(0, parseInt(String(count), 10) || 0);
    const label = n > 99 ? "99+" : String(n);

    const toggleBtn = document.getElementById("chat-toggle-btn");
    if (toggleBtn) {
      toggleBtn.classList.toggle("has-unread", n > 0);
    }

    const bubble = document.getElementById("unread-count");
    if (bubble) {
      if (n > 0) {
        bubble.textContent = label;
        bubble.style.display = "flex";
      } else {
        bubble.style.display = "none";
      }
    }

    const headerBadge = document.getElementById("header-messages-badge");
    if (headerBadge) {
      if (n > 0) {
        headerBadge.textContent = label;
        headerBadge.style.display = "flex";
      } else {
        headerBadge.style.display = "none";
      }
    }
  }

  async refreshUnreadBadgesOnce() {
    try {
      const response = await fetch(`/social/messages/unread/count`);
      if (!response.ok) {
        return;
      }
      const data = await response.json();
      this.updateUnreadBadges(data.unread_count ?? 0);
    } catch {
      /* ignore */
    }
  }

  minimizeMainPanel() {
    document.getElementById("chat-main-panel").classList.add("minimized");
    document.getElementById("chat-toggle-btn").classList.remove("active");
  }

  attachMobileSheetGestures() {
    const panel = document.getElementById("chat-main-panel");
    if (!panel) return;

    let startY = 0;
    let startX = 0;
    let dragging = false;

    panel.addEventListener(
      "touchstart",
      (e) => {
        if (panel.classList.contains("minimized")) return;
        const t = e.touches && e.touches[0];
        if (!t) return;
        startY = t.clientY;
        startX = t.clientX;
        dragging = true;
      },
      { passive: true },
    );

    panel.addEventListener(
      "touchmove",
      (e) => {
        if (!dragging) return;
        const t = e.touches && e.touches[0];
        if (!t) return;
        const dy = t.clientY - startY;
        const dx = t.clientX - startX;
        if (dy > 10 && Math.abs(dy) > Math.abs(dx) * 1.3) {
          e.preventDefault();
        }
      },
      { passive: false },
    );

    panel.addEventListener(
      "touchend",
      (e) => {
        if (!dragging) return;
        dragging = false;
        const t = e.changedTouches && e.changedTouches[0];
        if (!t) return;
        const dy = t.clientY - startY;
        const dx = t.clientX - startX;
        const isVertical = Math.abs(dy) > Math.abs(dx) * 1.3;
        if (isVertical && dy > 80) {
          this.minimizeMainPanel();
        }
      },
      { passive: true },
    );
  }

  async openComposeDialog() {
    const overlay = document.getElementById("chat-compose-overlay");
    const search = document.getElementById("chat-compose-search");
    overlay.classList.add("is-open");
    overlay.setAttribute("aria-hidden", "false");
    search.value = "";
    await this.renderComposeList("");
    requestAnimationFrame(() => search.focus());
  }

  closeComposeModal() {
    const overlay = document.getElementById("chat-compose-overlay");
    overlay.classList.remove("is-open");
    overlay.setAttribute("aria-hidden", "true");
  }

  async ensurePrivateUsersCache() {
    if (this.privateUsersCache && Array.isArray(this.privateUsersCache)) {
      return this.privateUsersCache;
    }
    const response = await fetch("/social/messages/api/users");
    if (!response.ok) {
      throw new Error("fetch users");
    }
    const users = await response.json();
    if (!Array.isArray(users)) {
      throw new Error("invalid json");
    }
    this.privateUsersCache = users;
    return users;
  }

  async renderComposeList(query) {
    const list = document.getElementById("chat-compose-list");
    list.innerHTML =
      '<div class="chat-compose-empty">Chargement…</div>';
    try {
      const users = await this.ensurePrivateUsersCache();
      const q = (query || "").toLowerCase().trim();
      const filtered = users.filter((u) => {
        const name = (u.name || u.username || "").toLowerCase();
        const un = (u.username || "").toLowerCase();
        return !q || name.includes(q) || un.includes(q);
      });
      if (filtered.length === 0) {
        list.innerHTML =
          '<div class="chat-compose-empty">Aucune personne trouvée.</div>';
        return;
      }
      list.innerHTML = filtered
        .map((u) => {
          const name = this.escapeHtml(u.name || u.username || "Utilisateur");
          const sub = u.username
            ? this.escapeHtml(u.username)
            : "";
          const initials = this.getInitials(u.name || u.username || "?");
          const color = this.colors[(u.id || 0) % this.colors.length];
          const av = u.avatarUrl
            ? `<img src="${this.escapeHtml(u.avatarUrl)}" alt="" width="40" height="40" style="border-radius:50%;object-fit:cover;">`
            : `<div class="conversation-avatar" style="background-color:${color};width:40px;height:40px;">${initials}</div>`;
          return `<button type="button" class="chat-compose-item" data-compose-id="${u.id}">
                ${av}
                <div><div class="chat-compose-item-name">${name}</div>${sub ? `<div class="chat-compose-item-sub">@${sub}</div>` : ""}</div>
            </button>`;
        })
        .join("");

      list.querySelectorAll(".chat-compose-item").forEach((btn) => {
        btn.addEventListener("click", () => {
          const id = btn.dataset.composeId;
          const nameEl = btn.querySelector(".chat-compose-item-name");
          const name = nameEl ? nameEl.textContent : "";
          const img = btn.querySelector("img");
          const avatar = img ? img.getAttribute("src") : "";
          this.closeComposeModal();
          this.openConversation(id, "user", name, avatar || null);
        });
      });
    } catch {
      list.innerHTML =
        '<div class="chat-compose-empty">Impossible de charger les contacts.</div>';
      this.showToast("Impossible de charger les contacts.");
    }
  }

  filterComposeList(query) {
    this.renderComposeList(query);
  }

  switchTab(tab) {
    this.currentTab = tab;

    document
      .querySelectorAll(".chat-tab")
      .forEach((t) => t.classList.remove("active"));
    document
      .querySelector(`.chat-tab[data-tab="${tab}"]`)
      .classList.add("active");

    document
      .querySelectorAll(".conversation-list")
      .forEach((l) => l.classList.remove("active"));
    if (tab === "private") {
      document.getElementById("private-conversations").classList.add("active");
    } else {
      document.getElementById("group-conversations").classList.add("active");
    }

    this.updateFilterRowVisibility();
    this.loadConversations();
  }

  async loadConversations() {
    try {
      const listContainer =
        this.currentTab === "private"
          ? document.getElementById("private-conversations")
          : document.getElementById("group-conversations");

      listContainer.innerHTML = '<div class="empty-state">Chargement...</div>';

      if (this.currentTab === "private") {
        const response = await fetch("/social/messages/api/users");
        if (!response.ok) {
          listContainer.innerHTML =
            '<div class="empty-state">Erreur de chargement</div>';
          this.showToast("Impossible de charger les conversations.");
          return;
        }
        const users = await response.json();
        if (!Array.isArray(users)) {
          listContainer.innerHTML =
            '<div class="empty-state">Erreur de chargement</div>';
          return;
        }
        this.privateUsersCache = users;

        this.renderConversationList(
          users.map((user) => ({
            id: user.id,
            type: "user",
            name: user.name || user.username,
            avatar: user.avatarUrl,
            lastMessage: user.lastMessage,
            lastMessageTime: user.lastMessageTime,
            unreadCount: user.unreadCount,
          })),
          listContainer,
        );
      } else {
        const response = await fetch("/social/messages/api/groups");
        if (!response.ok) {
          listContainer.innerHTML =
            '<div class="empty-state">Erreur de chargement</div>';
          this.showToast("Impossible de charger les groupes.");
          return;
        }
        const groups = await response.json();

        this.renderConversationList(
          groups.map((group) => ({
            id: group.id,
            type: "group",
            name: group.name,
            avatar: group.avatar,
          })),
          listContainer,
        );
      }
    } catch (error) {
      console.error("Error loading conversations:", error);
      this.showToast("Erreur réseau.");
    }
  }

  renderConversationList(conversations, container) {
    if (conversations.length === 0) {
      if (container.id === "private-conversations") {
        container.innerHTML = `
                <div class="empty-state empty-state--rich">
                    <p>Aucun contact pour le moment. Suivez des collègues sur le réseau ou démarrez une conversation.</p>
                    <button type="button" class="chat-empty-cta">Nouveau message</button>
                </div>`;
        container
          .querySelector(".chat-empty-cta")
          .addEventListener("click", () => this.openComposeDialog());
      } else {
        container.innerHTML =
          '<div class="empty-state">Aucun groupe disponible</div>';
      }
      return;
    }

    container.innerHTML = conversations
      .map((conv) => {
        const initials = this.getInitials(conv.name);
        const colorIndex = conv.id % this.colors.length;
        const color = this.colors[colorIndex];
        const unread = (conv.unreadCount || 0) > 0;
        const lastMessagePreview = conv.lastMessage
          ? conv.lastMessage.substring(0, 50) +
            (conv.lastMessage.length > 50 ? "..." : "")
          : "Aucun message";
        const timeAgo = conv.lastMessageTime
          ? this.formatTime(conv.lastMessageTime)
          : "";

        const avatarHtml = conv.avatar
          ? `<img src="${conv.avatar}" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">`
          : `<div class="conversation-avatar" style="background-color: ${color};">${initials}</div>`;

        const unreadClass = unread ? " conversation-item--unread" : "";

        return `
                <div class="conversation-item${unreadClass}" data-id="${conv.id}" data-type="${conv.type}" data-avatar="${conv.avatar || ""}" data-unread="${unread ? "1" : "0"}">
                    ${avatarHtml}
                    <div class="conversation-content">
                        <div class="conversation-header">
                            <div class="conversation-name">${this.escapeHtml(conv.name)}</div>
                            <div class="conversation-time">${timeAgo}</div>
                        </div>
                        <div class="conversation-preview">${this.escapeHtml(lastMessagePreview)}</div>
                    </div>
                    ${conv.unreadCount ? `<div class="conversation-badge">${conv.unreadCount}</div>` : ""}
                </div>
            `;
      })
      .join("");

    container.querySelectorAll(".conversation-item").forEach((item) => {
      item.addEventListener("click", () => {
        const id = item.dataset.id;
        const type = item.dataset.type;
        const avatar = item.dataset.avatar;
        this.openConversation(
          id,
          type,
          item.querySelector(".conversation-name").textContent,
          avatar,
        );
      });
    });

    if (container.id === "private-conversations") {
      this.applyPrivateListFilters();
    }
  }

  applyPrivateListFilters() {
    const q = (
      document.getElementById("chat-search-input")?.value || ""
    ).toLowerCase();
    const unreadOnly = this.listFilter === "unread";
    document
      .querySelectorAll("#private-conversations .conversation-item")
      .forEach((item) => {
        const name = item
          .querySelector(".conversation-name")
          .textContent.toLowerCase();
        const hasUnread = item.dataset.unread === "1";
        const matchSearch = !q || name.includes(q);
        const matchFilter = !unreadOnly || hasUnread;
        item.style.display = matchSearch && matchFilter ? "" : "none";
      });
  }

  async openConversation(conversationId, type, name, avatar = null) {
    if (this.openConversations.has(conversationId)) {
      return;
    }

    if (this.openConversations.size >= 2) {
      const firstKey = this.openConversations.keys().next().value;
      const convData = this.openConversations.get(firstKey);
      const closedName =
        convData?.panel
          ?.querySelector(".chat-sub-title")
          ?.textContent?.trim() || "";
      this.closeConversation(firstKey);
      if (closedName) {
        this.showToast(
          `« ${closedName} » a été fermé pour laisser place à une nouvelle fenêtre (max. 2).`,
        );
      }
    }

    const panel = document.createElement("div");
    panel.className = "chat-sub-panel";
    panel.dataset.conversationId = conversationId;

    const initials = this.getInitials(name);
    const color = this.colors[conversationId % this.colors.length];
    panel.style.setProperty("--chat-accent", color);

    const avatarHtml = avatar
      ? `<img src="${avatar}" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">`
      : `<div class="chat-sub-avatar" style="background-color: ${color};">${initials}</div>`;

    panel.innerHTML = `
            <div class="chat-sub-header">
                <div class="chat-sub-header-info">
                    ${avatarHtml}
                    <div class="chat-sub-header-details">
                        <h4 class="chat-sub-title">${this.escapeHtml(name)}</h4>
                        <div class="chat-subtitle-muted">Conversation ${type === "user" ? "privée" : "de groupe"}</div>
                    </div>
                </div>
                <button type="button" class="chat-close-btn" data-close="${conversationId}" aria-label="Fermer la conversation">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="chat-messages" id="messages-${conversationId}"></div>
            <div class="chat-input-area">
                <input type="text" class="chat-input" placeholder="Écrire un message..." data-input-for="${conversationId}" autocomplete="off">
                <button type="button" class="chat-send-btn" data-send-for="${conversationId}" title="Envoyer (Entrée)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                        <path d="M16.6915026,12.4744748 L3.50612381,13.2599618 C3.19218622,13.2599618 3.03521743,13.4170592 3.03521743,13.5741566 L1.15159189,20.0151496 C0.8376543,20.8006365 0.99,21.89 1.77946707,22.52 C2.41,22.99 3.50612381,23.1 4.13399899,22.9429026 L21.714504,14.0454487 C22.6563168,13.5741566 23.1272231,12.6315722 22.6563168,11.6889879 L4.13399899,1.05961247 C3.34915502,0.9 2.40734225,0.9 1.77946707,1.4429026 C0.994623095,2.23806617 0.837654326,3.33699531 1.15159189,4.12216889 L3.03521743,10.5631618 C3.03521743,10.7202592 3.19218622,10.8773566 3.50612381,10.8773566 L16.6915026,11.6889879 C16.6915026,11.6889879 17.1624089,11.6889879 17.1624089,11.0604506 L17.1624089,12.4744748 C17.1624089,12.4744748 17.1624089,12.4744748 16.6915026,12.4744748 Z"></path>
                    </svg>
                </button>
            </div>
        `;

    document.getElementById("chat-sub-panels-container").appendChild(panel);

    this.openConversations.set(conversationId, {
      panel,
      pollTimer: null,
      lastMessageCount: 0,
      type,
    });

    panel
      .querySelector("[data-close]")
      .addEventListener("click", () => this.closeConversation(conversationId));

    const input = panel.querySelector("[data-input-for]");
    const sendBtn = panel.querySelector("[data-send-for]");

    sendBtn.addEventListener("click", () =>
      this.sendMessage(conversationId, type),
    );

    sendBtn.disabled = true;
    input.addEventListener("input", () => {
      sendBtn.disabled = input.value.trim() === "";
    });

    input.addEventListener("keypress", (e) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        this.sendMessage(conversationId, type);
      }
    });

    await this.loadMessages(conversationId, type);
    this.startMessagePoller(conversationId, type);
  }

  async loadMessages(conversationId, type) {
    try {
      let url;
      if (type === "user") {
        url = `/social/messages/private/${conversationId}`;
      } else {
        url = `/social/messages/group/${conversationId}`;
      }

      const response = await fetch(url);
      const data = await response.json();
      const messages = data.messages || [];

      const convData = this.openConversations.get(conversationId);
      if (convData) {
        convData.lastMessageCount = messages.length;
      }

      this.renderMessages(conversationId, messages);

      if (type === "user") {
        await fetch(`/social/messages/mark-read/${conversationId}`, {
          method: "PATCH",
        });
        this.refreshUnreadBadgesOnce();
      }
    } catch (error) {
      console.error("Error loading messages:", error);
    }
  }

  renderMessages(conversationId, messages) {
    const container = document.getElementById(`messages-${conversationId}`);
    if (!container) return;

    const grouped = [];
    let lastDate = null;
    messages.forEach((msg) => {
      const d = new Date(msg.created_at || msg.timestamp).toLocaleDateString(
        "fr-FR",
        { day: "numeric", month: "long", year: "numeric" },
      );
      if (d !== lastDate) {
        grouped.push({ type: "separator", label: d });
        lastDate = d;
      }
      grouped.push({ type: "message", data: msg });
    });

    container.innerHTML = grouped
      .map((item) => {
        if (item.type === "separator") {
          return `<div class="message-date-separator"><span>${item.label}</span></div>`;
        }

        const msg = item.data;
        const isOwn =
          parseInt(msg.sender_id, 10) === parseInt(this.currentUserId, 10);
        const timeStr = this.formatTime(msg.created_at || msg.timestamp);
        return `
                <div class="message ${isOwn ? "own" : "other"}">
                    <div class="message-bubble">${this.escapeHtml(msg.content)}</div>
                    <div class="message-time">${timeStr}</div>
                </div>
            `;
      })
      .join("");

    container.scrollTop = container.scrollHeight;
  }

  async sendMessage(conversationId, type) {
    const inputElement = document.querySelector(
      `[data-input-for="${conversationId}"]`,
    );
    const sendBtn = document.querySelector(
      `[data-send-for="${conversationId}"]`,
    );
    const content = inputElement.value.trim();

    if (!content) return;

    const optimisticEl = this.appendOptimisticMessage(conversationId, content);
    inputElement.value = "";
    inputElement.dispatchEvent(new Event("input"));

    sendBtn.classList.add("is-loading");
    sendBtn.disabled = true;

    try {
      let url;
      let body;

      if (type === "user") {
        url = "/social/messages/private";
        body = {
          content,
          receiver_id: conversationId,
          sender_name: this.getCurrentUserName(),
          sender_avatar: this.getCurrentUserAvatar(),
        };
      } else {
        url = "/social/messages/group";
        body = {
          content,
          group_id: conversationId,
          sender_name: this.getCurrentUserName(),
          sender_avatar: this.getCurrentUserAvatar(),
        };
      }

      const response = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
      });

      if (response.ok) {
        await this.loadMessages(conversationId, type);
      } else {
        throw new Error(await response.text());
      }
    } catch (error) {
      console.error("Error sending message:", error);
      if (optimisticEl) {
        optimisticEl.classList.add("optimistic-error");
        const timeEl = optimisticEl.querySelector(".message-time");
        if (timeEl) {
          timeEl.textContent = "Non envoyé";
        }
      }
      inputElement.value = content;
      inputElement.dispatchEvent(new Event("input"));
      this.showToast("Message non envoyé. Vérifiez votre connexion et réessayez.");
    } finally {
      sendBtn.classList.remove("is-loading");
      sendBtn.disabled = inputElement.value.trim() === "";
    }
  }

  appendOptimisticMessage(conversationId, content) {
    const container = document.getElementById(`messages-${conversationId}`);
    if (!container) return null;

    const div = document.createElement("div");
    div.className = "message own optimistic";
    div.innerHTML = `<div class="message-bubble">${this.escapeHtml(content)}</div>
                     <div class="message-time">À l'instant</div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
    return div;
  }

  closeConversation(conversationId) {
    const convData = this.openConversations.get(conversationId);
    if (convData) {
      if (convData.pollTimer) {
        clearTimeout(convData.pollTimer);
      }
      convData.panel.remove();
      this.openConversations.delete(conversationId);
    }
  }

  filterConversations(query) {
    if (this.currentTab === "private") {
      this.applyPrivateListFilters();
    } else {
      const q = query.toLowerCase();
      document
        .querySelectorAll("#group-conversations .conversation-item")
        .forEach((item) => {
          const name = item
            .querySelector(".conversation-name")
            .textContent.toLowerCase();
          item.style.display = name.includes(q) ? "" : "none";
        });
    }
  }

  startMessagePoller(conversationId, type) {
    const run = async () => {
      const convData = this.openConversations.get(conversationId);
      if (!convData) {
        return;
      }
      try {
        let url;
        if (type === "user") {
          url = `/social/messages/private/${conversationId}`;
        } else {
          url = `/social/messages/group/${conversationId}`;
        }

        const response = await fetch(url);
        const data = await response.json();
        const messages = data.messages || [];

        if (convData && messages.length > convData.lastMessageCount) {
          convData.lastMessageCount = messages.length;
          this.renderMessages(conversationId, messages);
        }
      } catch {
        /* ignore */
      }

      const next = this.openConversations.get(conversationId);
      if (!next) {
        return;
      }
      const delay = document.hidden ? 30000 : 5000;
      next.pollTimer = setTimeout(run, delay);
    };

    const convData = this.openConversations.get(conversationId);
    if (convData) {
      const delay = document.hidden ? 30000 : 5000;
      convData.pollTimer = setTimeout(run, delay);
    }
  }

  scheduleUnreadPoll() {
    if (this.unreadPollTimer) {
      clearTimeout(this.unreadPollTimer);
      this.unreadPollTimer = null;
    }
    const delay = document.hidden ? 30000 : 10000;
    this.unreadPollTimer = setTimeout(async () => {
      this.unreadPollTimer = null;
      try {
        const response = await fetch(`/social/messages/unread/count`);
        const data = await response.json();
        const count = data.unread_count ?? 0;
        this.updateUnreadBadges(count);
      } catch {
        /* ignore */
      }
      this.scheduleUnreadPoll();
    }, delay);
  }

  formatTime(timestamp) {
    if (!timestamp) return "";

    try {
      const date = new Date(timestamp);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);

      if (diffMins < 1) return "À l'instant";
      if (diffMins < 60) return `${diffMins}m`;
      if (diffHours < 24) return `${diffHours}h`;
      if (diffDays < 7) return `${diffDays}j`;

      return date.toLocaleDateString("fr-FR", {
        month: "short",
        day: "numeric",
      });
    } catch {
      return "";
    }
  }

  getInitials(name) {
    return name
      .split(" ")
      .filter(Boolean)
      .map((word) => word[0])
      .join("")
      .toUpperCase()
      .substring(0, 2);
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  getCurrentUserName() {
    return document.body.dataset.userName || "User";
  }

  getCurrentUserAvatar() {
    return document.body.dataset.userAvatar || "";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  if (document.getElementById("chat-widget")) {
    window.chatWidget = new ChatWidget();
  }
});

/** Appel depuis le header social (toujours défini pour éviter les erreurs si le widget est absent). */
function openSocialMessenger() {
  if (window.chatWidget && typeof window.chatWidget.openMessenger === "function") {
    window.chatWidget.openMessenger();
  }
}
