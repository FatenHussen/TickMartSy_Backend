<div id="popup-campaign-root" aria-live="polite" aria-atomic="true"></div>

@once
    <script>
        (function () {
            const rootId = 'popup-campaign-root';
            const endpoint = @json(url('/api/popups/active'));
            const options = window.popupCampaignOptions || {};
            const params = new URLSearchParams({
                page_type: options.pageType ?? 'home',
                current_url: options.currentUrl ?? window.location.pathname,
            });

            const styleId = 'popup-campaign-styles';
            const styleContent = `
                #${rootId} {
                    position: fixed;
                    inset: 0;
                    display: none;
                    align-items: center;
                    justify-content: center;
                    background: rgba(0, 0, 0, 0.35);
                    z-index: 1100;
                    padding: 1rem;
                    text-align: left;
                }

                #${rootId}.show {
                    display: flex;
                }

                #${rootId}::before {
                    content: '';
                    position: absolute;
                    inset: 0;
                    background: rgba(0, 0, 0, 0.45);
                    z-index: 1;
                    backdrop-filter: blur(2px);
                    pointer-events: none;
                }

                #${rootId}.slide_in {
                    align-items: flex-end;
                    justify-content: flex-end;
                    padding: 1.5rem;
                    background: transparent;
                }
                #${rootId}.slide_in::before {
                    background: linear-gradient(180deg, rgba(0,0,0,0.2), rgba(0,0,0,0));
                }

                #${rootId}.fullscreen {
                    background: rgba(0, 0, 0, 0.8);
                    padding: 0;
                }

                .popup-card {
                    position: relative;
                    background: #ffffff;
                    border-radius: 18px;
                    padding: 2rem;
                    width: min(480px, 100%);
                    max-height: 90vh;
                    overflow: auto;
                    z-index: 2;
                    box-shadow: 0 25px 45px rgba(0, 0, 0, 0.25);
                    display: flex;
                    flex-direction: column;
                    gap: 1.25rem;
                    font-family: 'Inter', system-ui, sans-serif;
                }

                #${rootId}.slide_in .popup-card {
                    position: relative;
                    width: 360px;
                    border-radius: 16px;
                    animation: popup-slide 0.4s ease-out;
                }

                #${rootId}.fullscreen .popup-card {
                    width: 100%;
                    height: 100%;
                    border-radius: 0;
                    justify-content: flex-start;
                    padding: 2.5rem;
                }

                @keyframes popup-slide {
                    from {
                        transform: translateY(50%);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }

                .popup-close {
                    position: absolute;
                    top: 0.75rem;
                    right: 0.75rem;
                    background: transparent;
                    border: none;
                    font-size: 1.5rem;
                    cursor: pointer;
                    z-index: 3;
                }

                .popup-headline {
                    font-size: 1.85rem;
                    font-weight: 700;
                    margin: 0;
                }

                .popup-subheadline {
                    color: #5b5f66;
                    margin: 0;
                }

                .popup-description {
                    color: #31353f;
                    margin: 0;
                    line-height: 1.4;
                }

                .popup-buttons {
                    display: flex;
                    gap: 0.75rem;
                    flex-wrap: wrap;
                    margin-top: 0.5rem;
                }

                .popup-buttons button {
                    flex: 1;
                    padding: 0.85rem 1rem;
                    border: none;
                    font-size: 1rem;
                    border-radius: 999px;
                    cursor: pointer;
                }

                .popup-primary {
                    background: #ff6a3d;
                    color: white;
                }

                .popup-secondary {
                    background: #f1f3f5;
                    color: #1f2933;
                }

                .popup-media img,
                .popup-media video {
                    width: 100%;
                    border-radius: 12px;
                    max-height: 260px;
                    object-fit: cover;
                    display: block;
                }

                .popup-form {
                    display: flex;
                    flex-direction: column;
                    gap: 0.75rem;
                }

                .popup-form input,
                .popup-form textarea {
                    width: 100%;
                    padding: 0.75rem 1rem;
                    border-radius: 10px;
                    border: 1px solid #d5d9dd;
                    font-size: 1rem;
                }
            `;

            if (!document.getElementById(styleId)) {
                const style = document.createElement('style');
                style.id = styleId;
                style.textContent = styleContent;
                document.head.appendChild(style);
            }

            document.addEventListener('DOMContentLoaded', () => {
                const root = document.getElementById(rootId);
                if (!root) return;

                fetch(`${endpoint}?${params.toString()}`, {
                    credentials: 'include',
                })
                    .then((response) => response.json())
                    .then((result) => result.data)
                    .then((campaign) => {
                        if (!campaign) return;

                        const shouldShow = canShowCampaign(campaign);
                        if (!shouldShow) return;

                        schedulePopup(campaign, root);
                    })
                    .catch((error) => {
                        console.warn('Popup campaign failed', error);
                    });
            });

            function canShowCampaign(campaign) {
                const stampKey = `popup:${campaign.slug}:last_shown`;
                const impressionsKey = `popup:${campaign.slug}:impressions`;

                const impressions = Number(localStorage.getItem(impressionsKey)) || 0;
                if (campaign.frequency.max_impressions && campaign.frequency.max_impressions > 0) {
                    if (impressions >= campaign.frequency.max_impressions) {
                        return false;
                    }
                }

                const lastShown = Number(localStorage.getItem(stampKey)) || 0;
                if (campaign.frequency.show_every && campaign.frequency.show_every > 0 && lastShown) {
                    const elapsedMinutes = (Date.now() - lastShown) / 60000;
                    if (elapsedMinutes < campaign.frequency.show_every) {
                        return false;
                    }
                }

                return true;
            }

            function schedulePopup(campaign, root) {
                let displayed = false;

                const show = () => {
                    if (displayed || !isElementInDocument(root)) return;
                    displayed = true;
                    renderPopup(campaign, root);
                };

                const trigger = campaign.trigger.type;
                const value = Number(campaign.trigger.value);

                switch (trigger) {
                    case 'delay':
                        setTimeout(show, (value || 2) * 1000);
                        break;
                    case 'scroll':
                        const threshold = Math.min(Math.max(value || 25, 5), 100);
                        const onScroll = () => {
                            const scrollable = document.body.scrollHeight - window.innerHeight;
                            const percent = scrollable > 0
                                ? Math.round((window.scrollY / scrollable) * 100)
                                : 100;
                            if (percent >= threshold) {
                                window.removeEventListener('scroll', onScroll);
                                show();
                            }
                        };
                        window.addEventListener('scroll', onScroll);
                        break;
                    case 'exit_intent':
                        const onMouseMove = (event) => {
                            if (event.clientY <= 30 && event.relatedTarget === null) {
                                window.removeEventListener('mouseout', onMouseMove);
                                show();
                            }
                        };
                        window.addEventListener('mouseout', onMouseMove);
                        break;
                    default:
                        show();
                }
            }

            function renderPopup(campaign, root) {
                const { type } = campaign;
                root.className = '';
                root.classList.add('show', type);
                root.innerHTML = '';

                const card = document.createElement('div');
                card.className = 'popup-card';

                const closeButton = document.createElement('button');
                closeButton.className = 'popup-close';
                closeButton.type = 'button';
                closeButton.innerHTML = '&times;';
                closeButton.addEventListener('click', () => closePopup(root));

                card.appendChild(closeButton);

                if (campaign.media?.path) {
                    const mediaWrapper = document.createElement('div');
                    mediaWrapper.className = 'popup-media';
                    if (campaign.media.type === 'video') {
                        const video = document.createElement('video');
                        video.src = campaign.media.path;
                        video.autoplay = true;
                        video.muted = true;
                        video.loop = true;
                        mediaWrapper.appendChild(video);
                    } else {
                        const img = document.createElement('img');
                        img.src = campaign.media.path;
                        img.alt = campaign.headline;
                        mediaWrapper.appendChild(img);
                    }
                    card.appendChild(mediaWrapper);
                }

                const headline = document.createElement('h2');
                headline.className = 'popup-headline';
                headline.textContent = campaign.content.headline;
                card.appendChild(headline);

                if (campaign.content.subheadline) {
                    const subheadline = document.createElement('p');
                    subheadline.className = 'popup-subheadline';
                    subheadline.textContent = campaign.content.subheadline;
                    card.appendChild(subheadline);
                }

                if (campaign.content.description) {
                    const description = document.createElement('p');
                    description.className = 'popup-description';
                    description.textContent = campaign.content.description;
                    card.appendChild(description);
                }

                if (campaign.form.enabled && Array.isArray(campaign.form.fields) && campaign.form.fields.length) {
                    const form = document.createElement('form');
                    form.className = 'popup-form';
                    form.innerHTML = campaign.form.fields
                        .map((field) => {
                            const label = field.charAt(0).toUpperCase() + field.slice(1);
                            if (field === 'custom_text') {
                                return `<textarea name="${field}" placeholder="${label}" rows="3"></textarea>`;
                            }
                            return `<input name="${field}" placeholder="${label}" required />`;
                        })
                        .join('');

                    form.addEventListener('submit', (event) => {
                        event.preventDefault();
                        const formData = Object.fromEntries(new FormData(form).entries());
                        trackClick(campaign, { formData });
                        closePopup(root);
                    });

                    card.appendChild(form);
                }

                const buttonWrapper = document.createElement('div');
                buttonWrapper.className = 'popup-buttons';

                const primary = document.createElement('button');
                primary.type = 'button';
                primary.className = 'popup-primary';
                primary.textContent = campaign.buttons.primary ?? 'Take action';
                primary.addEventListener('click', () => {
                    trackClick(campaign);
                    handleCta(campaign);
                    closePopup(root);
                });
                buttonWrapper.appendChild(primary);

                if (campaign.buttons.secondary) {
                    const secondary = document.createElement('button');
                    secondary.type = 'button';
                    secondary.className = 'popup-secondary';
                    secondary.textContent = campaign.buttons.secondary;
                    secondary.addEventListener('click', () => {
                        closePopup(root);
                    });
                    buttonWrapper.appendChild(secondary);
                }

                card.appendChild(buttonWrapper);
                root.appendChild(card);
                persistImpression(campaign);
                trackView(campaign);
            }

            function handleCta(campaign) {
                const { type, value } = campaign.cta;
                if (!value) {
                    return;
                }

                if (type === 'coupon') {
                    navigator.clipboard?.writeText(value);
                    alert('Coupon code copied!');
                    return;
                }

                if (type === 'form') {
                    return;
                }

                window.location.assign(value);
            }

            function persistImpression(campaign) {
                const stampKey = `popup:${campaign.slug}:last_shown`;
                const impressionsKey = `popup:${campaign.slug}:impressions`;
                const impressions = Number(localStorage.getItem(impressionsKey)) || 0;
                localStorage.setItem(impressionsKey, String(impressions + 1));
                localStorage.setItem(stampKey, String(Date.now()));
            }

            function trackView(campaign) {
                fetch(`/api/popups/${campaign.id}/track-view`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        page_type: window.popupCampaignOptions?.pageType || 'home',
                        current_url: window.popupCampaignOptions?.currentUrl || window.location.pathname,
                    }),
                });
            }

            function trackClick(campaign, extra = {}) {
                fetch(`/api/popups/${campaign.id}/track-click`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        page_type: window.popupCampaignOptions?.pageType || 'home',
                        current_url: window.popupCampaignOptions?.currentUrl || window.location.pathname,
                        ...extra,
                    }),
                });
            }

            function closePopup(root) {
                root.className = '';
                root.innerHTML = '';
            }

            function isElementInDocument(el) {
                return el && document.body.contains(el);
            }
        })();
    </script>
@endonce
