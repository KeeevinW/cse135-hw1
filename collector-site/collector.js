(function () {
  'use strict';

  // --- Configuration ---
  const config = {
    // TODO: Update this to your reporting server endpoint in Part 4/5
    endpoint: 'https://reporting.xuanye.site/api/log', 
  };

  // --- Session Identity ---
  function getSessionId() {
    let sid = sessionStorage.getItem('_collector_sid');
    if (!sid) {
      sid = Math.random().toString(36).substring(2) + Date.now().toString(36);
      sessionStorage.setItem('_collector_sid', sid);
    }
    return sid;
  }

  const sessionID = getSessionId();
  const entryTime = Date.now();

  // --- Data Batching ---
  let activityQueue = [];

  // Send data to server
  function sendPayload(payload) {
    if (!config.endpoint) return;
    const json = JSON.stringify(payload);
    
    if (navigator.sendBeacon) {
      navigator.sendBeacon(config.endpoint, new Blob([json], { type: 'application/json' }));
    } else {
      fetch(config.endpoint, {
        method: 'POST',
        body: json,
        headers: { 'Content-Type': 'application/json' },
        keepalive: true
      }).catch(console.error);
    }
  }

  // Flush the activity queue periodically (every 3 seconds)
  setInterval(() => {
    if (activityQueue.length > 0) {
      sendPayload({
        session: sessionID,
        page: window.location.href,
        type: 'activity_batch',
        data: activityQueue
      });
      activityQueue = []; // Clear queue after sending
    }
  }, 3000);

  //Static Data
  let imagesAllowed = false;
  
  // Manual check for images
  const testImg = new Image();
  testImg.onload = () => { imagesAllowed = true; };
  testImg.src = "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="; // 1x1 transparent pixel

  function getStaticData() {
    return {
      userAgent: navigator.userAgent,
      language: navigator.language,
      cookiesEnabled: navigator.cookieEnabled,
      jsAllowed: true, // If this script is running, JS is allowed
      imagesAllowed: imagesAllowed,
      cssAllowed: document.styleSheets.length > 0, // Basic check if stylesheets are loaded
      screenWidth: window.screen.width,
      screenHeight: window.screen.height,
      windowWidth: window.innerWidth,
      windowHeight: window.innerHeight,
      connectionType: navigator.connection ? navigator.connection.effectiveType : 'unknown'
    };
  }

  // Performance Data
  function getPerformanceData() {
    const perfData = window.performance.timing;
    return {
      timingObject: perfData,
      startedLoading: perfData.navigationStart,
      endedLoading: perfData.loadEventEnd,
      totalLoadTime: perfData.loadEventEnd - perfData.navigationStart
    };
  }

  // Send Static and Performance Data on Window Load
  window.addEventListener('load', () => {
    setTimeout(() => {
      sendPayload({
        session: sessionID,
        page: window.location.href,
        type: 'initial_load',
        entryTime: entryTime,
        static: getStaticData(),
        performance: getPerformanceData()
      });
    }, 0);
  });

  // Activity Tracking

  // Track Errors
  window.addEventListener('error', (event) => {
    activityQueue.push({ event: 'error', message: event.message, filename: event.filename, lineno: event.lineno });
  });

  // Track Mouse Clicks
  window.addEventListener('mousedown', (e) => {
    activityQueue.push({ event: 'click', x: e.clientX, y: e.clientY, button: e.button });
  });

  // Track Mouse Movement (Throttled to avoid massive data bloat)
  let lastMouseMove = 0;
  window.addEventListener('mousemove', (e) => {
    const now = Date.now();
    if (now - lastMouseMove > 500) { // Only record every 500ms
      activityQueue.push({ event: 'mousemove', x: e.clientX, y: e.clientY });
      lastMouseMove = now;
    }
  });

  // Track Scrolling
  let lastScroll = 0;
  window.addEventListener('scroll', () => {
    const now = Date.now();
    if (now - lastScroll > 500) {
      activityQueue.push({ event: 'scroll', scrollX: window.scrollX, scrollY: window.scrollY });
      lastScroll = now;
    }
  });

  // Track Keyboard
  window.addEventListener('keydown', (e) => {
    activityQueue.push({ event: 'keydown', key: e.key });
  });
  window.addEventListener('keyup', (e) => {
    activityQueue.push({ event: 'keyup', key: e.key });
  });

  // Idle Time Tracking
  let idleTimer;
  let lastActivityTime = Date.now();
  let isIdle = false;

  function resetIdleTimer() {
    const now = Date.now();
    
    // If we were idle, record that the break ended and how long it lasted
    if (isIdle) {
      const breakDuration = now - lastActivityTime;
      activityQueue.push({ event: 'idle_end', durationMs: breakDuration });
      isIdle = false;
    }
    
    lastActivityTime = now;
    clearTimeout(idleTimer);
    
    // Set timer for 2 seconds (2000ms) of inactivity
    idleTimer = setTimeout(() => {
      isIdle = true;
      activityQueue.push({ event: 'idle_start', timestamp: Date.now() });
    }, 2000);
  }

  // Bind idle reset to user actions
  ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'].forEach(evt => {
    window.addEventListener(evt, resetIdleTimer, true);
  });

  // Exit Tracking
  window.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') {
      // Force send any remaining data in the queue immediately
      if (activityQueue.length > 0) {
         sendPayload({ session: sessionID, page: window.location.href, type: 'activity_batch', data: activityQueue });
         activityQueue = [];
      }
      // Send exit event
      sendPayload({ session: sessionID, page: window.location.href, type: 'page_exit', exitTime: Date.now() });
    }
  });

})();