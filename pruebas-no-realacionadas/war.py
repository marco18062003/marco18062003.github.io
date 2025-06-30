import keyboard
import time

print("Simulación de keylogger (educativo, no guarda datos)")
print("Presiona teclas, se mostrará lo que un keylogger detectaría. Ctrl+C para salir.")

try:
    while True:
        event = keyboard.read_event()
        if event.event_type == keyboard.KEY_DOWN:
            print(f"Tecla presionada: {event.name}")
        time.sleep(0.1)
except KeyboardInterrupt:
    print("Simulación terminada.")