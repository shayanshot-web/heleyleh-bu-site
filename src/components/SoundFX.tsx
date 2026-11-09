'use client';

import { useCallback, useEffect, useRef, useState } from 'react';

/* صداهای تولیدشده با WebAudio (بدون فایل صوتی). مرورگرها پخش خودکار را نمی‌پذیرند؛
   پس صدا فقط بعد از زدن دکمهٔ بلندگو فعال می‌شود و انتخاب کاربر یادآوری می‌شود. */

type Ctx = AudioContext;
type Maker = (c: Ctx, out: AudioNode) => void;

const KEY = 'helileh-sound';
const noiseCache = new WeakMap<Ctx, AudioBuffer>();

function noise(c: Ctx): AudioBuffer {
  let b = noiseCache.get(c);
  if (!b) {
    b = c.createBuffer(1, c.sampleRate * 2, c.sampleRate);
    const d = b.getChannelData(0);
    for (let i = 0; i < d.length; i++) d[i] = Math.random() * 2 - 1;
    noiseCache.set(c, b);
  }
  return b;
}

function env(c: Ctx, out: AudioNode, t: number, peak: number, attack: number, dur: number): GainNode {
  const g = c.createGain();
  g.gain.setValueAtTime(0.0001, t);
  g.gain.exponentialRampToValueAtTime(Math.max(peak, 0.0002), t + attack);
  g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
  g.connect(out);
  return g;
}

function tone(c: Ctx, out: AudioNode, o: { f: number; to?: number; type?: OscillatorType; at?: number; dur: number; vol: number; attack?: number }) {
  const t = c.currentTime + (o.at ?? 0);
  const osc = c.createOscillator();
  osc.type = o.type ?? 'sine';
  osc.frequency.setValueAtTime(o.f, t);
  if (o.to) osc.frequency.exponentialRampToValueAtTime(o.to, t + o.dur);
  osc.connect(env(c, out, t, o.vol, o.attack ?? 0.01, o.dur));
  osc.start(t);
  osc.stop(t + o.dur + 0.05);
}

function hiss(
  c: Ctx,
  out: AudioNode,
  o: { type: BiquadFilterType; f: number; to?: number; q?: number; at?: number; dur: number; vol: number; attack?: number },
) {
  const t = c.currentTime + (o.at ?? 0);
  const src = c.createBufferSource();
  src.buffer = noise(c);
  src.loop = true;
  const f = c.createBiquadFilter();
  f.type = o.type;
  f.Q.value = o.q ?? 0.7;
  f.frequency.setValueAtTime(o.f, t);
  if (o.to) f.frequency.exponentialRampToValueAtTime(o.to, t + o.dur);
  src.connect(f);
  f.connect(env(c, out, t, o.vol, o.attack ?? 0.01, o.dur));
  src.start(t, Math.random());
  src.stop(t + o.dur + 0.05);
}


/* شاخ/کَرنای باستانی: دو نت اکتاو و پنجم با موج دندانه‌ای و فیلتر پایین‌گذر */
function horn(c: Ctx, out: AudioNode, o: { f: number; at?: number; dur: number; vol: number; attack: number; cutoff: number }) {
  const t = c.currentTime + (o.at ?? 0);
  const lp = c.createBiquadFilter();
  lp.type = 'lowpass';
  lp.Q.value = 2;
  lp.frequency.setValueAtTime(o.cutoff * 0.5, t);
  lp.frequency.exponentialRampToValueAtTime(o.cutoff, t + o.attack);
  lp.connect(env(c, out, t, o.vol, o.attack, o.dur));
  [1, 1.005].forEach((det) => {
    const osc = c.createOscillator();
    osc.type = 'sawtooth';
    osc.frequency.setValueAtTime(o.f * det, t);
    osc.connect(lp);
    osc.start(t);
    osc.stop(t + o.dur + 0.05);
  });
}

/* ضربهٔ طبل حماسی: بدنهٔ کم‌فرکانس و ضربهٔ پوست */
function drum(c: Ctx, out: AudioNode, at: number, f: number, vol: number) {
  tone(c, out, { f, to: f * 0.38, at, dur: 0.55, vol, attack: 0.004 });
  hiss(c, out, { type: 'lowpass', f: 260, at, dur: 0.22, vol: vol * 0.6, attack: 0.003 });
  hiss(c, out, { type: 'bandpass', f: 1400, q: 1.5, at, dur: 0.04, vol: vol * 0.25, attack: 0.002 });
}

/* نالهٔ مرغ دریایی */
function gull(c: Ctx, out: AudioNode, at: number, base: number, vol: number) {
  tone(c, out, { f: base * 0.8, to: base * 1.55, at, dur: 0.13, vol, type: 'sawtooth', attack: 0.02 });
  tone(c, out, { f: base * 1.55, to: base * 0.8, at: at + 0.12, dur: 0.3, vol, type: 'sawtooth', attack: 0.02 });
  tone(c, out, { f: base * 1.6, to: base * 3.1, at, dur: 0.13, vol: vol * 0.35, type: 'triangle', attack: 0.02 });
  tone(c, out, { f: base * 3.1, to: base * 1.6, at: at + 0.12, dur: 0.3, vol: vol * 0.3, type: 'triangle', attack: 0.02 });
}

/* ظرف پر از روغن داغ: هیسِ پیوسته، هزاران ترکِ ریز، حباب‌های قل‌قل و پاشیدن روغن (~۴ ثانیه) */
function sizzle(c: Ctx, out: AudioNode) {
  const D = 4;
  // ریختن ماهی در روغن: انفجار اولیه
  hiss(c, out, { type: 'highpass', f: 2500, q: 0.5, dur: 0.9, vol: 0.4, attack: 0.012 });
  hiss(c, out, { type: 'bandpass', f: 1100, q: 0.7, dur: 0.6, vol: 0.28, attack: 0.01 });
  // بستر هیس پیوسته که کم‌کم آرام می‌شود، با نوسان تصادفی شدت
  for (let t = 0; t < D; t += 0.18) {
    const fade = Math.pow(1 - t / D, 0.55);
    hiss(c, out, { type: 'highpass', f: 4200, q: 0.4, at: t, dur: 0.3, vol: (0.13 + Math.random() * 0.1) * fade, attack: 0.05 });
    hiss(c, out, { type: 'bandpass', f: 6500, q: 0.6, at: t, dur: 0.3, vol: (0.09 + Math.random() * 0.08) * fade, attack: 0.05 });
  }
  // ترک‌های ریز متراکم (حدود ۷۰ در ثانیه در ابتدا)
  for (let i = 0; i < 340; i++) {
    const u = Math.random();
    const at = D * (1 - Math.pow(1 - u, 1.6)) * 0.97;
    const fade = Math.pow(1 - at / D, 0.5);
    hiss(c, out, {
      type: 'bandpass',
      f: 1500 + Math.random() * 6000,
      q: 3 + Math.random() * 3,
      at,
      dur: 0.008 + Math.random() * 0.02,
      vol: (0.1 + Math.random() * 0.25) * fade,
      attack: 0.001,
    });
  }
  // پاشیدن روغن: ترک‌های درشت‌تر و کم‌فرکانس‌تر
  for (let i = 0; i < 34; i++) {
    const at = Math.pow(Math.random(), 1.4) * D * 0.9;
    hiss(c, out, {
      type: 'bandpass',
      f: 500 + Math.random() * 1000,
      q: 1.5,
      at,
      dur: 0.04 + Math.random() * 0.05,
      vol: (0.2 + Math.random() * 0.25) * Math.pow(1 - at / D, 0.4),
      attack: 0.002,
    });
  }
  // حباب‌های قل‌قل روغن
  for (let i = 0; i < 40; i++) {
    const at = Math.random() * D * 0.92;
    const f = 220 + Math.random() * 420;
    tone(c, out, { f, to: f * (1.6 + Math.random() * 0.8), at, dur: 0.04 + Math.random() * 0.05, vol: (0.1 + Math.random() * 0.1) * Math.pow(1 - at / D, 0.4), attack: 0.004 });
  }
}

/* بستر کف‌زدن موج روی ساحل: نویز با نوسان آهسته شدت (بی‌وقفه و بدون «عبور» یک‌باره) */
function surfBed(c: Ctx, out: AudioNode, at: number, dur: number, vol: number) {
  const t = c.currentTime + at;
  const src = c.createBufferSource();
  src.buffer = noise(c);
  src.loop = true;
  const lp = c.createBiquadFilter();
  lp.type = 'lowpass';
  lp.frequency.value = 1100;
  lp.Q.value = 0.3;
  const amp = c.createGain();
  amp.gain.value = 0.75;
  const lfo = c.createOscillator();
  lfo.frequency.value = 0.22 + Math.random() * 0.08;
  const lfoDepth = c.createGain();
  lfoDepth.gain.value = 0.2;
  lfo.connect(lfoDepth);
  lfoDepth.connect(amp.gain);
  src.connect(lp);
  lp.connect(amp);
  amp.connect(env(c, out, t, vol, dur * 0.25, dur));
  src.start(t, Math.random());
  lfo.start(t);
  src.stop(t + dur + 0.05);
  lfo.stop(t + dur + 0.05);
}

/* یک موج کامل: بالا آمدن، شکستن با کف سفید، و پس‌کشیدن آب روی شن */
function wave(c: Ctx, out: AudioNode, at: number, dur: number, vol: number) {
  const t = c.currentTime + at;
  const peak = dur * 0.38;
  const src = c.createBufferSource();
  src.buffer = noise(c);
  src.loop = true;
  const lp = c.createBiquadFilter();
  lp.type = 'lowpass';
  lp.Q.value = 0.5;
  lp.frequency.setValueAtTime(260, t);
  lp.frequency.exponentialRampToValueAtTime(2200, t + peak);
  lp.frequency.exponentialRampToValueAtTime(320, t + dur);
  const g = c.createGain();
  g.gain.setValueAtTime(0.0001, t);
  g.gain.exponentialRampToValueAtTime(vol, t + peak);
  g.gain.linearRampToValueAtTime(vol * 0.38, t + peak + (dur - peak) * 0.45);
  g.gain.linearRampToValueAtTime(0.0001, t + dur);
  src.connect(lp);
  lp.connect(g);
  g.connect(out);
  src.start(t, Math.random());
  src.stop(t + dur + 0.05);
  // کف: خش‌خش ریز و بلند هنگام شکستن که آرام‌آرام می‌خوابد
  hiss(c, out, { type: 'highpass', f: 3600, q: 0.4, at: at + peak * 0.8, dur: dur * 0.7, vol: vol * 0.22, attack: peak * 0.25 });
  for (let i = 0; i < 24; i++) {
    hiss(c, out, { type: 'bandpass', f: 4000 + Math.random() * 4000, q: 4, at: at + peak + Math.random() * dur * 0.5, dur: 0.012, vol: vol * 0.15, attack: 0.002 });
  }
}

const sounds: Record<string, Maker> = {
  /* موج دریا: بستر کف‌زدن و سه موج پی‌درپی با طول‌های متفاوت (~۶ ثانیه) */
  sea: (c, o) => {
    surfBed(c, o, 0, 7.2, 0.34);
    wave(c, o, 0, 3.8, 0.4);
    wave(c, o, 2.0, 4.0, 0.36);
    wave(c, o, 4.1, 3.2, 0.3);
  },
  /* معرفی و تاریخچه: طبل رزم و کَرنای باستانی و حماسی (~۵ ثانیه) */
  about: (c, o) => {
    drum(c, o, 0, 120, 0.6);
    drum(c, o, 0.5, 100, 0.5);
    drum(c, o, 1.0, 120, 0.6);
    drum(c, o, 1.5, 80, 0.8);
    drum(c, o, 2.7, 100, 0.45);
    drum(c, o, 3.2, 70, 0.6);
    drum(c, o, 4.1, 60, 0.7);
    horn(c, o, { f: 98, at: 0.05, dur: 5.4, vol: 0.2, attack: 0.9, cutoff: 900 });
    horn(c, o, { f: 147, at: 0.6, dur: 4.9, vol: 0.14, attack: 0.9, cutoff: 1100 });
    horn(c, o, { f: 196, at: 1.5, dur: 2.9, vol: 0.09, attack: 0.8, cutoff: 1300 });
    horn(c, o, { f: 130.8, at: 2.7, dur: 2.0, vol: 0.1, attack: 0.6, cutoff: 1000 });
  },
  /* اقامتگاه: طبیعت کنار ساحل و آرامش؛ موج نرم، نسیم و چهچههٔ دور پرنده (~۷ ثانیه) */
  stay: (c, o) => {
    surfBed(c, o, 0, 8, 0.2);
    wave(c, o, 0.2, 4.6, 0.22);
    wave(c, o, 2.5, 4.6, 0.19);
    hiss(c, o, { type: 'bandpass', f: 700, to: 450, q: 0.8, dur: 6.5, vol: 0.035, attack: 2 });
    tone(c, o, { f: 220, dur: 6.5, vol: 0.04, attack: 2 });
    tone(c, o, { f: 330, at: 0.3, dur: 6, vol: 0.028, attack: 2 });
    [0.9, 1.05, 1.2, 2.4, 2.55, 3.9, 4.05, 4.2, 5.3].forEach((at, i) => {
      const f = 2900 + (i % 4) * 420;
      tone(c, o, { f, to: f * 1.25, at, dur: 0.07, vol: 0.04, attack: 0.01 });
    });
  },
  /* جاذبه‌ها: موج دریا و مرغ دریایی (~۶ ثانیه) */
  attractions: (c, o) => {
    surfBed(c, o, 0, 7.2, 0.3);
    wave(c, o, 0, 3.8, 0.38);
    wave(c, o, 2.1, 4.0, 0.34);
    wave(c, o, 4.2, 3.2, 0.28);
    gull(c, o, 0.6, 1150, 0.07);
    gull(c, o, 1.35, 1000, 0.055);
    gull(c, o, 3.3, 1200, 0.065);
    gull(c, o, 3.95, 1050, 0.05);
  },
  /* رستوران‌ها: روغن داغ و ماهی سرخ‌شونده */
  restaurants: (c, o) => sizzle(c, o),
  /* تیک قطب‌نما */
  experiences: (c, o) => {
    tone(c, o, { f: 1500, type: 'triangle', dur: 0.05, vol: 0.2, attack: 0.002 });
    tone(c, o, { f: 1100, type: 'triangle', at: 0.11, dur: 0.05, vol: 0.16, attack: 0.002 });
    tone(c, o, { f: 2400, at: 0.2, dur: 0.5, vol: 0.05, attack: 0.01 });
  },
  /* شاتر دوربین */
  gallery: (c, o) => {
    hiss(c, o, { type: 'highpass', f: 2500, dur: 0.05, vol: 0.3, attack: 0.002 });
    hiss(c, o, { type: 'highpass', f: 1800, at: 0.07, dur: 0.07, vol: 0.25, attack: 0.002 });
  },
  /* خش قلم */
  blog: (c, o) => {
    for (let i = 0; i < 4; i++) hiss(c, o, { type: 'bandpass', f: 3500 + i * 300, q: 2, at: i * 0.07, dur: 0.06, vol: 0.16, attack: 0.01 });
  },
  /* ناقوس */
  events: (c, o) => {
    tone(c, o, { f: 880, dur: 1.4, vol: 0.2 });
    tone(c, o, { f: 1320, dur: 1, vol: 0.09 });
    tone(c, o, { f: 2217, dur: 0.6, vol: 0.05 });
  },
  /* ضربهٔ سفال */
  shop: (c, o) => {
    tone(c, o, { f: 320, to: 150, dur: 0.12, vol: 0.4, attack: 0.003 });
    tone(c, o, { f: 760, to: 700, at: 0.01, dur: 0.18, vol: 0.1, type: 'triangle' });
  },
  /* تنبک */
  culture: (c, o) => {
    tone(c, o, { f: 190, to: 70, dur: 0.3, vol: 0.55, attack: 0.004 });
    hiss(c, o, { type: 'bandpass', f: 1800, dur: 0.05, vol: 0.25, attack: 0.002 });
    tone(c, o, { f: 260, to: 110, at: 0.18, dur: 0.2, vol: 0.3, attack: 0.004 });
  },
  /* زنگ تلفن */
  contact: (c, o) => {
    [0, 0.26].forEach((at) => {
      tone(c, o, { f: 440, at, dur: 0.2, vol: 0.14, type: 'triangle', attack: 0.01 });
      tone(c, o, { f: 480, at, dur: 0.2, vol: 0.14, type: 'triangle', attack: 0.01 });
    });
  },
  /* غذا: همان جلزولز روغن داغ */
  food: (c, o) => sizzle(c, o),
  /* تیک ملایم پیوندها */
  tick: (c, o) => {
    tone(c, o, { f: 1250, to: 1000, dur: 0.05, vol: 0.09, attack: 0.003 });
  },
  /* دکمه‌ها */
  pop: (c, o) => {
    tone(c, o, { f: 420, to: 820, dur: 0.1, vol: 0.2, attack: 0.005 });
  },
  /* نشان‌ها و کارت‌ها */
  drop: (c, o) => {
    tone(c, o, { f: 900, to: 420, dur: 0.16, vol: 0.18, attack: 0.005 });
    tone(c, o, { f: 1350, to: 630, at: 0.01, dur: 0.14, vol: 0.06, attack: 0.005 });
  },
};

sounds.craft = sounds.shop;
sounds.phone = sounds.contact;

export default function SoundFX({ onLabel, offLabel, hint }: { onLabel: string; offLabel: string; hint: string }) {
  const [on, setOn] = useState(false);
  const ctxRef = useRef<Ctx | null>(null);
  const outRef = useRef<AudioNode | null>(null);
  const onRef = useRef(false);
  const voiceRef = useRef<GainNode | null>(null);
  const lastRef = useRef<{ el: Element | null; t: number }>({ el: null, t: 0 });

  const ensure = useCallback((): Ctx | null => {
    if (ctxRef.current) {
      if (ctxRef.current.state === 'suspended') void ctxRef.current.resume();
      return ctxRef.current;
    }
    const AC = window.AudioContext || (window as unknown as { webkitAudioContext?: typeof AudioContext }).webkitAudioContext;
    if (!AC) return null;
    const c = new AC();
    const comp = c.createDynamicsCompressor();
    const master = c.createGain();
    master.gain.value = 0.7;
    comp.connect(master);
    master.connect(c.destination);
    ctxRef.current = c;
    outRef.current = comp;
    return c;
  }, []);

  /* قطع نرم صدای در حال پخش (وقتی موس از روی آیکن بیرون می‌رود) */
  const stopVoice = useCallback(() => {
    const c = ctxRef.current;
    const v = voiceRef.current;
    if (!c || !v) return;
    voiceRef.current = null;
    const t = c.currentTime;
    v.gain.cancelScheduledValues(t);
    v.gain.setValueAtTime(v.gain.value, t);
    v.gain.linearRampToValueAtTime(0, t + 0.12);
    window.setTimeout(() => {
      try {
        v.disconnect();
      } catch {
        /* ignore */
      }
    }, 300);
  }, []);

  const play = useCallback(
    (name: string) => {
      if (!onRef.current) return;
      const c = ensure();
      const make = sounds[name] ?? sounds.drop;
      if (c && outRef.current && c.state === 'running') {
        stopVoice();
        const voice = c.createGain();
        voice.connect(outRef.current);
        voiceRef.current = voice;
        make(c, voice);
      }
    },
    [ensure, stopVoice],
  );

  useEffect(() => {
    let saved = false;
    try {
      saved = localStorage.getItem(KEY) === '1';
    } catch {
      /* حالت خصوصی */
    }
    onRef.current = saved;
    setOn(saved);

    // اگر کاربر قبلاً صدا را روشن کرده، با اولین کلیک/لمس/کلید مرورگر اجازهٔ پخش می‌دهد
    const unlock = () => {
      if (onRef.current) ensure();
    };
    window.addEventListener('pointerdown', unlock, { once: true });
    window.addEventListener('keydown', unlock, { once: true });

    // روی لمس‌صفحه «هاور» وجود ندارد، ولی مرورگر با شروع هر لمس/اسکرول رویداد pointerover را روی چیزی که زیر انگشت است می‌فرستد
    // (مثلاً بخش بزرگ بالای صفحه که صدای موج دارد). پس برای لمس فقط «ضربهٔ عمدی» روی دکمه/پیوند صدا دارد.
    let lastType = '';
    const track = (e: PointerEvent) => {
      lastType = e.pointerType;
    };
    const tap = (e: MouseEvent) => {
      if (!onRef.current) return;
      const type = (e as PointerEvent).pointerType || lastType;
      if (type !== 'touch') return;
      const target = e.target as Element | null;
      const el = target && target.closest ? target.closest('[data-sound]') : null;
      if (!el || !el.matches('a, button, [role="button"]')) return; // کلیک روی پس‌زمینه/بخش‌های بزرگ بی‌صدا
      const now = performance.now();
      if (now - lastRef.current.t < 140) return;
      lastRef.current.t = now;
      play(el.getAttribute('data-sound') || 'drop');
    };
    const over = (e: Event) => {
      if (!onRef.current) return;
      if (e.type === 'pointerover' && (e as PointerEvent).pointerType === 'touch') return; // لمس/اسکرول صدا نمی‌سازد
      if (e.type === 'focusin' && lastType === 'touch') return; // فوکوس ناشی از ضربه را خود tap پوشش می‌دهد
      const target = e.target as Element | null;
      const el = target && target.closest ? target.closest('[data-sound]') : null;
      if (!el) {
        lastRef.current.el = null;
        stopVoice();
        return;
      }
      if (el === lastRef.current.el) return;
      lastRef.current.el = el;
      const now = performance.now();
      if (now - lastRef.current.t < 140) return;
      lastRef.current.t = now;
      play(el.getAttribute('data-sound') || 'drop');
    };
    // خروج موس از روی آیکن (یا از پنجره): صدا قطع شود. روی لمس‌صفحه‌ها «هاور» نداریم، پس قطع نمی‌کنیم.
    const out = (e: PointerEvent) => {
      if (e.pointerType === 'touch') return;
      const cur = lastRef.current.el;
      if (!cur) return;
      const to = e.relatedTarget as Node | null;
      if (to && cur.contains(to)) return;
      lastRef.current.el = null;
      stopVoice();
    };
    const blur = () => {
      lastRef.current.el = null;
      stopVoice();
    };
    document.addEventListener('pointerdown', track, { passive: true, capture: true });
    document.addEventListener('click', tap, { passive: true });
    document.addEventListener('pointerover', over, { passive: true });
    document.addEventListener('pointerout', out, { passive: true });
    document.addEventListener('focusin', over);
    document.addEventListener('focusout', blur);
    return () => {
      document.removeEventListener('pointerdown', track, true);
      document.removeEventListener('click', tap);
      document.removeEventListener('pointerover', over);
      document.removeEventListener('pointerout', out);
      document.removeEventListener('focusin', over);
      document.removeEventListener('focusout', blur);
      window.removeEventListener('pointerdown', unlock);
      window.removeEventListener('keydown', unlock);
    };
  }, [ensure, play, stopVoice]);

  const toggle = () => {
    const next = !on;
    onRef.current = next;
    setOn(next);
    if (!next) stopVoice();
    try {
      localStorage.setItem(KEY, next ? '1' : '0');
    } catch {
      /* ignore */
    }
    if (next) {
      const c = ensure();
      if (c) {
        const go = () => sounds.sea(c, outRef.current as AudioNode);
        if (c.state === 'running') go();
        else void c.resume().then(go);
      }
    }
  };

  return (
    <button
      type="button"
      className={on ? 'sound-toggle is-on' : 'sound-toggle'}
      onClick={toggle}
      aria-pressed={on}
      aria-label={on ? onLabel : offLabel}
      title={hint}
    >
      <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <path d="M4 9.5v5h3.5L12 18.5v-13L7.5 9.5z" fill="currentColor" fillOpacity=".25" />
        {on ? (
          <>
            <path className="snd w1" d="M15.5 9.5a3.6 3.6 0 0 1 0 5" />
            <path className="snd w2" d="M18 7a7 7 0 0 1 0 10" />
          </>
        ) : (
          <path d="M16 9.5l5 5M21 9.5l-5 5" />
        )}
      </svg>
    </button>
  );
}
