# BIM AI Recognition Expansion

Apply these project-specific decisions to future implementation and documentation.

## Agreed direction

- Keep every taught and recognized sign exclusively Bahasa Isyarat Malaysia (BIM).
- Expand one-hand static recognition before attempting two-hand recognition.
- Candidate number scope is BIM numbers 0–9, but only after every handshape is verified against a reliable BIM reference and confirmed to be static and one-handed.
- Add approximately 5–10 visually distinct, BIM-verified, static one-hand alphabet signs after the number classifier is reliable.
- Prefer approximately 15–20 accurately recognized signs over claiming the full alphabet with poor reliability.
- Connect verified signs to a timed AI Practical Challenge with randomized targets, stable-hold confirmation, scoring, review, and MySQL result storage.
- Treat two-hand static recognition as a later optional phase. Add it only when an authentic BIM sign requires two hands, not merely to increase the number of classes.
- Do not introduce dynamic gesture recognition, temporal video models, LSTMs, Transformers, or another sign language.

## Acceptance gates

- Classify every proposed sign as one-hand/two-hand and static/dynamic before implementation.
- Use MediaPipe hand landmarks with Fingerpose/custom geometric rules within the existing browser-based stack.
- Test candidates across multiple users, lighting conditions, distances, backgrounds, and handedness.
- Do not promote a sign into the practical assessment until its accuracy and confusion with neighboring classes are documented.
- Keep the implementation feasible on an Acer Nitro V15 built-in webcam and appropriate for a polytechnic diploma FYP.
