(() => {
    'use strict';
    const section = document.querySelector('.projects-section');
    if (!section) return;
    const gallery = section.querySelector('.project-gallery');
    const toolbar = section.querySelector('.projects-toolbar');
    const layoutButton = section.querySelector('.projects-layout-button');
    const motionButton = section.querySelector('.projects-motion-button');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
    const count = section.querySelectorAll('[data-project-index]').length;
    const lanes = [...section.querySelectorAll('.project-lane')].map((element, index) => ({
        element, track: element.querySelector('.project-track'), original: element.querySelector('.project-set'),
        direction: index === 0 ? -1 : 1, width: 0, x: 0, nudge: 0, velocity: 0,
    }));
    let grid = false;
    let paused = false;
    let kinetic = false;
    let visible = false;
    let hovered = null;
    let focused = false;
    let drag = null;
    let suppressClickUntil = 0;
    let raf = 0;
    let previousTime = 0;
    let scrollImpulse = 0;
    let lastScroll = window.scrollY;
    const mod = (value, size) => ((value % size) + size) % size;
    const wrap = lane => { if (lane.width > 0) lane.x = -lane.width - mod(-lane.x - lane.width, lane.width); };
    const render = lane => { lane.track.style.transform = `translate3d(${lane.x.toFixed(2)}px,0,0)`; };
    const canAutoMove = () => !paused && !hovered && !focused && !drag;
    const updateState = () => {
        section.classList.toggle('motion-paused', paused);
        section.classList.toggle('is-interacting', Boolean(hovered || focused || drag));
        section.classList.toggle('is-offscreen', !visible || document.hidden);
        motionButton.setAttribute('aria-pressed', String(paused));
        motionButton.setAttribute('aria-label', paused ? motionButton.dataset.playLabel : motionButton.dataset.pauseLabel);
        motionButton.firstElementChild.textContent = paused ? '▷' : 'Ⅱ';
    };
    function tick(now) {
        raf = 0;
        if (!kinetic || !visible || document.hidden) { previousTime = 0; return; }
        const dt = previousTime ? Math.min((now - previousTime) / 1000, .04) : 1 / 60;
        previousTime = now;
        const autoplay = canAutoMove();
        let settling = false;
        lanes.forEach(lane => {
            if (!lane.width || drag?.lane === lane) return;
            if (autoplay) lane.x += lane.direction * (23 + scrollImpulse) * dt;
            if (Math.abs(lane.nudge) > .1) {
                const amount = lane.nudge * (1 - Math.exp(-dt * 9));
                lane.x += amount;
                lane.nudge -= amount;
                settling = true;
            } else lane.nudge = 0;
            if (Math.abs(lane.velocity) > 1) {
                lane.x += lane.velocity * dt;
                lane.velocity *= Math.exp(-dt * .72);
                settling = true;
            } else lane.velocity = 0;
            // Focused original links stay in the visible copy, including at loop boundaries.
            if (!focused) wrap(lane);
            render(lane);
        });
        scrollImpulse *= Math.exp(-dt * 4);
        if (autoplay || settling) raf = requestAnimationFrame(tick);
        else previousTime = 0;
    }
    function wake() {
        updateState();
        if (!raf && kinetic && visible && !document.hidden) raf = requestAnimationFrame(tick);
    }
    function stop() {
        cancelAnimationFrame(raf);
        raf = 0;
        previousTime = 0;
        scrollImpulse = 0;
        lanes.forEach(lane => { lane.velocity = 0; });
    }
    const resetMagnet = card => {
        if (!card) return;
        ['--mag-x', '--mag-y', '--tilt-x', '--tilt-y'].forEach(property => card.style.removeProperty(property));
    };
    function cloneSet(original, alternate = false) {
        const clone = original.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        clone.querySelectorAll('a, button').forEach(link => link.tabIndex = -1);
        clone.querySelectorAll('[id]').forEach(element => element.removeAttribute('id'));
        clone.querySelectorAll('.project-card').forEach(card => {
            if (alternate) card.dataset.projectVariant = 'alternate';
        });
        return clone;
    }
    function measure() {
        stop();
        if (drag) {
            const previousDrag = drag;
            drag = null;
            previousDrag.lane.element.classList.remove('is-dragging');
            if (previousDrag.active) suppressClickUntil = performance.now() + 350;
            if (previousDrag.lane.element.hasPointerCapture(previousDrag.id)) previousDrag.lane.element.releasePointerCapture(previousDrag.id);
        }
        resetMagnet(hovered);
        hovered = null;
        kinetic = !grid && !reduced.matches && count > 1;
        section.classList.toggle('is-kinetic', kinetic);
        toolbar.hidden = reduced.matches || count < 2;
        layoutButton.setAttribute('aria-pressed', String(!kinetic));
        layoutButton.querySelector('span').textContent = kinetic ? layoutButton.dataset.gridLabel : layoutButton.dataset.carouselLabel;
        if (kinetic) gallery.tabIndex = 0;
        else gallery.removeAttribute('tabindex');
        lanes.forEach((lane, index) => {
            const phase = lane.width ? mod(-lane.x - lane.width, lane.width) / lane.width : index * .38;
            [...lane.track.children].filter(child => child !== lane.original).forEach(child => child.remove());
            lane.track.style.transform = '';
            lane.nudge = 0;
            lane.velocity = 0;
            if (!kinetic || !lane.original.children.length) { lane.width = 0; return; }
            // The loop includes two colorways, so wrapping never changes a visible card's color.
            lane.width = parseFloat(getComputedStyle(lane.original).width) * 2;
            if (!lane.width) return;
            // One preceding cycle, the accessible original + its alternate colorway,
            // then enough complete cycles to fill ultrawide screens.
            lane.track.prepend(cloneSet(lane.original), cloneSet(lane.original, true));
            lane.track.append(cloneSet(lane.original, true));
            const trailing = Math.ceil(lane.element.clientWidth / lane.width) + 1;
            for (let i = 0; i < trailing; i++) lane.track.append(cloneSet(lane.original), cloneSet(lane.original, true));
            lane.x = -lane.width * (1 + phase);
            const focusedCard = lane.original.contains(document.activeElement) ? document.activeElement.closest('.project-card') : null;
            if (focusedCard) lane.x = -lane.width - focusedCard.offsetLeft + (lane.element.clientWidth - focusedCard.offsetWidth) / 2;
            render(lane);
        });
        scrollImpulse = 0;
        wake();
    }
    const move = direction => {
        if (!kinetic) return;
        lanes.forEach(lane => {
            const card = lane.original.firstElementChild;
            if (card) lane.nudge += -direction * (parseFloat(getComputedStyle(card).width) + parseFloat(getComputedStyle(lane.original).gap));
        });
        wake();
    };
    motionButton.addEventListener('click', () => {
        paused = !paused;
        scrollImpulse = 0;
        lanes.forEach(lane => { lane.velocity = 0; lane.nudge = 0; });
        resetMagnet(hovered);
        wake();
    });
    layoutButton.addEventListener('click', () => { grid = !grid; measure(); });
    section.querySelectorAll('[data-project-direction]').forEach(button => button.addEventListener('click', () => move(Number(button.dataset.projectDirection))));
    gallery.addEventListener('keydown', event => {
        if (event.target !== gallery || !['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
        event.preventDefault();
        move(event.key === 'ArrowRight' ? 1 : -1);
    });

    gallery.addEventListener('pointerover', event => {
        if (event.pointerType !== 'mouse' || !kinetic) return;
        const card = event.target.closest('.project-card');
        if (hovered === card) return;
        resetMagnet(hovered);
        hovered = card;
        scrollImpulse = 0;
        wake();
    });
    gallery.addEventListener('pointerleave', () => {
        resetMagnet(hovered);
        hovered = null;
        wake();
    });
    gallery.addEventListener('pointermove', event => {
        if (!kinetic || paused || drag || !hovered || !finePointer.matches || event.pointerType !== 'mouse') return;
        const box = hovered.getBoundingClientRect();
        const x = Math.max(-.5, Math.min(.5, (event.clientX - box.left) / box.width - .5));
        const y = Math.max(-.5, Math.min(.5, (event.clientY - box.top) / box.height - .5));
        hovered.style.setProperty('--mag-x', `${(x * 15).toFixed(2)}px`);
        hovered.style.setProperty('--mag-y', `${(y * 10 - 5).toFixed(2)}px`);
        hovered.style.setProperty('--tilt-x', `${(-y * 5).toFixed(2)}deg`);
        hovered.style.setProperty('--tilt-y', `${(x * 6).toFixed(2)}deg`);
    });

    lanes.forEach(lane => {
        lane.element.addEventListener('pointerdown', event => {
            if (!kinetic || drag || event.button !== 0 || !event.isPrimary) return;
            drag = {lane, id: event.pointerId, startX: event.clientX, startY: event.clientY, lastX: event.clientX, time: performance.now(), active: false, gain: event.pointerType === 'touch' ? 1.55 : 1};
            lanes.forEach(item => { item.velocity = 0; });
            scrollImpulse = 0;
            lane.nudge = 0;
            resetMagnet(hovered);
            wake();
        });
        lane.element.addEventListener('pointermove', event => {
            if (!drag || drag.lane !== lane || drag.id !== event.pointerId) return;
            const dx = event.clientX - drag.startX;
            const dy = event.clientY - drag.startY;
            if (!drag.active && Math.abs(dy) > 8 && Math.abs(dy) > Math.abs(dx)) { drag = null; wake(); return; }
            if (!drag.active && Math.abs(dx) > 7 && Math.abs(dx) > Math.abs(dy)) {
                drag.active = true;
                lane.element.setPointerCapture(event.pointerId);
                lane.element.classList.add('is-dragging');
            }
            if (!drag.active) return;
            event.preventDefault();
            const now = performance.now();
            const delta = (event.clientX - drag.lastX) * drag.gain;
            const sample = Math.max(-2400, Math.min(2400, delta / Math.max(8, now - drag.time) * 1000));
            lane.velocity += (sample - lane.velocity) * .5;
            lane.x += delta;
            wrap(lane);
            render(lane);
            drag.lastX = event.clientX;
            drag.time = now;
        });
        const endDrag = event => {
            // Touch starts with implicit capture on the image/link. Its loss bubbles
            // when capture moves to the lane; that transfer is not the end of a drag.
            if (event.type === 'lostpointercapture' && event.target !== lane.element) return;
            if (!drag || drag.lane !== lane || drag.id !== event.pointerId) return;
            if (drag.active) {
                suppressClickUntil = performance.now() + 350;
                // Keep a flick's direction all the way back to cruising speed.
                // Holding before release dissipates the launch instead of firing a stale fling.
                const recency = Math.exp(-Math.max(0, performance.now() - drag.time - 60) / 160);
                const boost = drag.gain > 1 ? 1.6 : 1.15;
                lane.velocity = Math.max(-2600, Math.min(2600, lane.velocity * boost * recency));
                if (gallery.contains(document.activeElement)) document.activeElement.blur();
                focused = false;
            } else lane.velocity = 0;
            if (paused || event.type !== 'pointerup') lane.velocity = 0;
            if (Math.abs(lane.velocity) > 80) {
                lane.direction = Math.sign(lane.velocity);
                // The second row answers the gesture in the opposite direction.
                lanes.forEach(other => {
                    if (other === lane) return;
                    other.direction = -lane.direction;
                    other.velocity = -lane.velocity * .32;
                    other.nudge = 0;
                });
            } else lane.velocity = 0;
            drag = null;
            lane.element.classList.remove('is-dragging');
            if (lane.element.hasPointerCapture(event.pointerId)) lane.element.releasePointerCapture(event.pointerId);
            wake();
        };
        ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(type => lane.element.addEventListener(type, endDrag));
    });
    // A pointer released outside a lane without starting a drag must not freeze playback.
    window.addEventListener('pointerup', () => { if (drag && !drag.active) { drag = null; wake(); } });
    gallery.addEventListener('click', event => {
        if (performance.now() < suppressClickUntil) { event.preventDefault(); event.stopPropagation(); }
    }, true);
    gallery.addEventListener('dragstart', event => event.preventDefault());

    gallery.addEventListener('focusin', event => {
        focused = true;
        lanes.forEach(lane => { lane.velocity = 0; lane.nudge = 0; });
        if (kinetic) {
            const lane = lanes.find(item => item.original.contains(event.target));
            const card = event.target.closest('.project-card');
            if (lane && card) {
                lane.velocity = 0;
                lane.nudge = 0;
                lane.x = -lane.width - card.offsetLeft + (lane.element.clientWidth - card.offsetWidth) / 2;
                render(lane);
                // Native focus scrolling must not displace the overflow-clipped gallery.
                requestAnimationFrame(() => { gallery.scrollLeft = 0; });
            }
        }
        wake();
    });
    gallery.addEventListener('focusout', () => {
        queueMicrotask(() => {
            focused = gallery.contains(document.activeElement);
            wake();
        });
    });

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(entries => {
            visible = entries[entries.length - 1].isIntersecting;
            if (!visible) stop();
            wake();
        }, {threshold: 0}).observe(gallery);
    } else visible = true;
    window.addEventListener('scroll', () => {
        const delta = window.scrollY - lastScroll;
        lastScroll = window.scrollY;
        if (!kinetic || !visible || !canAutoMove()) return;
        scrollImpulse = Math.min(240, scrollImpulse + Math.min(Math.abs(delta), 80) * 1.4);
        wake();
    }, {passive: true});
    let resizeFrame = 0;
    window.addEventListener('resize', () => {
        cancelAnimationFrame(resizeFrame);
        resizeFrame = requestAnimationFrame(measure);
    }, {passive: true});
    reduced.addEventListener('change', measure);
    document.addEventListener('visibilitychange', () => { if (document.hidden) stop(); wake(); });
    window.addEventListener('pagehide', stop);
    window.addEventListener('pageshow', wake);
    measure();
    document.fonts.ready.then(measure);
})();
