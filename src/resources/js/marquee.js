// トップのブログ: 自動で右から左へ流れつつ、指(スワイプ)やマウス(ドラッグ)でも動かせるスライド
//
// しくみ:
//   ・同じカードの並びを3回続けて並べておく(1回分の幅 = period)
//   ・表示位置をつねに「2回目の並び」の範囲に保つ。はみ出したら period ぶん戻す
//     → 見た目は同じなので、つなぎ目なく無限にループして見える
//   ・ユーザーが触っている間は自動スクロールを止め、離して少したったら再開する

const SPEED = 40;          // 自動で流れる速さ(px/秒)
const RESUME_DELAY = 2000; // 操作をやめてから自動再開するまで(ミリ秒)

function setupMarquee(el) {
    const track = el.querySelector('.marquee-track');
    if (!track) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let period = track.scrollWidth / 3;
    let pos = period;          // 小数で位置を持つ(scrollLeft は整数に丸められることがあるため)
    let hovering = false;      // マウスが乗っている
    let touching = false;      // 指で触っている / マウスでドラッグ中
    let lastInteraction = 0;   // 最後に操作した時刻
    let lastTime = null;

    const apply = () => { el.scrollLeft = pos; };

    // 2回目の並びの範囲 [period, 2*period) に戻す
    const wrap = () => {
        if (period <= 0) return;
        if (pos >= period * 2) pos -= period;
        if (pos < period) pos += period;
    };

    apply();

    // 画面サイズが変わったら幅を測り直す
    window.addEventListener('resize', () => {
        const ratio = period > 0 ? pos / period : 1;
        period = track.scrollWidth / 3;
        pos = period * ratio;
        wrap();
        apply();
    });

    // ユーザーのスクロール(スワイプ・ホイールなど)を位置に反映する
    el.addEventListener('scroll', () => {
        if (Math.abs(el.scrollLeft - pos) > 1) {
            pos = el.scrollLeft;
            lastInteraction = performance.now();
            const before = pos;
            wrap();
            if (pos !== before) apply();
        }
    }, { passive: true });

    // 指で触っている間は止める
    el.addEventListener('touchstart', () => { touching = true; }, { passive: true });
    el.addEventListener('touchend', () => { touching = false; lastInteraction = performance.now(); });
    el.addEventListener('touchcancel', () => { touching = false; lastInteraction = performance.now(); });

    // マウスが乗っている間は止める
    el.addEventListener('mouseenter', () => { hovering = true; });
    el.addEventListener('mouseleave', () => { hovering = false; lastInteraction = performance.now(); });

    // マウスでつかんで横にドラッグできるようにする
    let dragStartX = 0;
    let dragStartPos = 0;
    let dragged = false;

    el.addEventListener('pointerdown', (e) => {
        if (e.pointerType !== 'mouse' || e.button !== 0) return;
        touching = true;
        dragged = false;
        dragStartX = e.clientX;
        dragStartPos = pos;
        el.classList.add('is-dragging');
    });

    window.addEventListener('pointermove', (e) => {
        if (e.pointerType !== 'mouse' || !el.classList.contains('is-dragging')) return;
        const dx = e.clientX - dragStartX;
        if (Math.abs(dx) > 5) dragged = true;
        pos = dragStartPos - dx;
        wrap();
        // ループで戻したぶん、ドラッグの基準もずらす
        dragStartPos = pos + dx;
        apply();
    });

    window.addEventListener('pointerup', (e) => {
        if (e.pointerType !== 'mouse' || !el.classList.contains('is-dragging')) return;
        el.classList.remove('is-dragging');
        touching = false;
        lastInteraction = performance.now();
    });

    // ドラッグしたときは、離した瞬間にリンクが開かないようにする
    el.addEventListener('click', (e) => {
        if (dragged) {
            e.preventDefault();
            e.stopPropagation();
            dragged = false;
        }
    }, true);

    // 画像やリンクをつかんだときのブラウザ標準のドラッグを止める
    el.addEventListener('dragstart', (e) => e.preventDefault());

    // 動きを減らす設定の人には自動では流さない(手で動かすのはOK)
    if (reduceMotion) return;

    const tick = (now) => {
        if (lastTime !== null) {
            const dt = (now - lastTime) / 1000;
            const idle = now - lastInteraction > RESUME_DELAY;
            if (!hovering && !touching && idle) {
                pos += SPEED * dt;
                wrap();
                apply();
            }
        }
        lastTime = now;
        requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
}

export function initMarquees() {
    document.querySelectorAll('[data-marquee]').forEach(setupMarquee);
}
