import argparse
import json
import os
import re
import subprocess
import sys
import tempfile

# Configure standard encoding for Windows console & pipes
if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')
if sys.stderr.encoding != 'utf-8':
    sys.stderr.reconfigure(encoding='utf-8', errors='replace')


def get_video_duration_ffprobe(video_path):
    """Attempt to get video duration in seconds using ffprobe."""
    try:
        cmd = [
            "ffprobe", "-v", "error",
            "-show_entries", "format=duration",
            "-of", "default=noprint_wrappers=1:nokey=1",
            video_path
        ]
        res = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True, check=True)
        dur = float(res.stdout.strip())
        if dur > 0:
            return dur
    except Exception:
        pass
    return None


def get_video_duration_ffmpeg(video_path):
    """Fallback: inspect duration from ffmpeg stderr output."""
    try:
        cmd = ["ffmpeg", "-i", video_path]
        res = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        # Match Duration: 00:00:37.45
        m = re.search(r"Duration:\s*(\d+):(\d+):(\d+(?:\.\d+)?)", res.stderr)
        if m:
            h, mi, s = float(m.group(1)), float(m.group(2)), float(m.group(3))
            return h * 3600 + mi * 60 + s
    except Exception:
        pass
    return None


def extract_posters(video_path, posters_dir, base_slug, timestamp_sec):
    """Extract dual high-quality WebP and JPG posters at given timestamp."""
    os.makedirs(posters_dir, exist_ok=True)
    jpg_filename = f"{base_slug}.jpg"
    webp_filename = f"{base_slug}.webp"
    jpg_path = os.path.join(posters_dir, jpg_filename)
    webp_path = os.path.join(posters_dir, webp_filename)

    ts_str = f"{timestamp_sec:.2f}"

    # Extract JPG
    try:
        cmd_jpg = [
            "ffmpeg", "-y", "-ss", ts_str,
            "-i", video_path,
            "-vframes", "1",
            "-q:v", "2",
            jpg_path
        ]
        subprocess.run(cmd_jpg, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, check=True)
    except Exception as e:
        sys.stderr.write(f"Warning: JPG poster extraction failed: {e}\n")

    # Extract WebP
    try:
        cmd_webp = [
            "ffmpeg", "-y", "-ss", ts_str,
            "-i", video_path,
            "-vframes", "1",
            "-quality", "85",
            webp_path
        ]
        subprocess.run(cmd_webp, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, check=True)
    except Exception as e:
        sys.stderr.write(f"Warning: WebP poster extraction failed: {e}\n")

    return {
        "jpg": jpg_filename if os.path.exists(jpg_path) else None,
        "webp": webp_filename if os.path.exists(webp_path) else None,
    }


def transcribe_video_audio(video_path):
    """Extract audio stream and transcribe using faster-whisper."""
    temp_wav = os.path.join(tempfile.gettempdir(), f"ddt_vid_audio_{os.getpid()}.wav")
    try:
        # Extract 16kHz mono audio
        cmd = [
            "ffmpeg", "-y", "-i", video_path,
            "-vn", "-ar", "16000", "-ac", "1", "-c:a", "pcm_s16le",
            temp_wav
        ]
        subprocess.run(cmd, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, check=True)

        if not os.path.exists(temp_wav) or os.path.getsize(temp_wav) == 0:
            return {
                "transcript": "",
                "english_translation": "",
                "language": "unknown",
                "language_probability": 0.0,
                "audio_duration": 0
            }

        # Read audio into float32 numpy array
        import wave
        import numpy as np
        with wave.open(temp_wav, "rb") as wf:
            n_frames = wf.getnframes()
            frames = wf.readframes(n_frames)
            audio = np.frombuffer(frames, dtype=np.int16).astype(np.float32) / 32768.0
            audio_dur = n_frames / 16000.0

        # Transcribe using faster-whisper
        from faster_whisper import WhisperModel
        model = WhisperModel("base", device="cpu", compute_type="int8")

        segments, info = model.transcribe(audio, beam_size=5)
        text_list = [seg.text.strip() for seg in segments]
        full_transcript = " ".join(text_list).strip()

        # If non-English, translate to English
        english_translation = full_transcript
        if info.language != "en" and full_transcript:
            try:
                segments_en, _ = model.transcribe(audio, task="translate", beam_size=5)
                en_list = [seg.text.strip() for seg in segments_en]
                english_translation = " ".join(en_list).strip()
            except Exception:
                english_translation = full_transcript

        return {
            "transcript": full_transcript,
            "english_translation": english_translation,
            "language": info.language,
            "language_probability": round(float(info.language_probability), 3),
            "audio_duration": round(audio_dur, 2)
        }

    except Exception as e:
        sys.stderr.write(f"Warning: Audio transcription error: {e}\n")
        return {
            "transcript": "",
            "english_translation": "",
            "language": "unknown",
            "language_probability": 0.0,
            "audio_duration": 0
        }
    finally:
        if os.path.exists(temp_wav):
            try:
                os.remove(temp_wav)
            except Exception:
                pass


def main():
    parser = argparse.ArgumentParser(description="Analyze customer video review using FFmpeg and faster-whisper")
    parser.add_argument("--video", required=True, help="Path to input video file")
    parser.add_argument("--posters-dir", required=True, help="Directory to save generated posters")
    parser.add_argument("--slug", required=True, help="Base slug for poster naming")
    parser.add_argument("--skip-transcription", action="store_true", help="Skip transcription (duration and poster only)")

    args = parser.parse_args()

    video_path = os.path.abspath(args.video)
    if not os.path.exists(video_path):
        print(json.dumps({"success": False, "error": f"Video file not found: {video_path}"}))
        sys.exit(1)

    # 1. Determine duration
    dur = get_video_duration_ffprobe(video_path)
    if dur is None:
        dur = get_video_duration_ffmpeg(video_path)

    # 2. Transcribe audio if not skipped
    transcription_data = {
        "transcript": "",
        "english_translation": "",
        "language": "unknown",
        "language_probability": 0.0,
        "audio_duration": 0
    }
    if not args.skip_transcription:
        transcription_data = transcribe_video_audio(video_path)

    if dur is None or dur <= 0:
        dur = transcription_data.get("audio_duration", 30.0)
    dur = max(1.0, float(dur))

    # 3. Determine optimal poster timestamp (~15% to 20% into video, clamped)
    if dur > 6.0:
        ts = min(max(3.0, dur * 0.18), dur - 1.0)
    else:
        ts = max(0.5, dur * 0.5)

    # 4. Extract posters
    posters = extract_posters(video_path, args.posters_dir, args.slug, ts)

    output = {
        "success": True,
        "duration": round(dur, 2),
        "duration_int": max(1, int(round(dur))),
        "poster_timestamp": round(ts, 2),
        "poster_webp": posters["webp"],
        "poster_jpg": posters["jpg"],
        "transcript": transcription_data["transcript"],
        "english_translation": transcription_data["english_translation"],
        "language": transcription_data["language"],
        "language_probability": transcription_data["language_probability"]
    }

    print(json.dumps(output, ensure_ascii=False))


if __name__ == "__main__":
    main()
