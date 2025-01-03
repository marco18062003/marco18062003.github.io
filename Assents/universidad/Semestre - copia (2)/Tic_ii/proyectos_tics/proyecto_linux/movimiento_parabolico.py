from vpython import *

# Introducción explicativa


# Datos de gravedad y velocidad de escape para planetas
gravedad_planetas = {
    "Mercurio": 3.7,
    "Venus": 8.87,
    "Tierra": 9.81,
    "Marte": 3.71,
    "Júpiter": 24.79,
    "Saturno": 10.44,
    "Urano": 8.69,
    "Neptuno": 11.15
}

velocidad_escape = {
    "Mercurio": 4250,
    "Venus": 10360,
    "Tierra": 11190,
    "Marte": 5030,
    "Júpiter": 59540,
    "Saturno": 35500,
    "Urano": 21300,
    "Neptuno": 23560
}

# Selección del planeta o gravedad
print("Planetas disponibles: Mercurio, Venus, Tierra, Marte, Júpiter, Saturno, Urano, Neptuno")
print("También puedes ingresar un valor personalizado para la gravedad (en m/s^2).")
planeta = input("Seleccione el planeta para ajustar la gravedad o ingrese un valor: ")

# Obtener gravedad y velocidad de escape del planeta seleccionado o usar el valor ingresado
if planeta in gravedad_planetas:
    g = gravedad_planetas[planeta]
    v_escape = velocidad_escape[planeta]
    print(f"Has seleccionado {planeta}. Gravedad: {g} m/s^2. Velocidad de escape: {v_escape} m/s.")
else:
    try:
        g = float(planeta)  # Convertir el valor ingresado a float
        v_escape = None  # No se puede obtener velocidad de escape para valores personalizados
        print(f"Has ingresado una gravedad personalizada: {g} m/s^2.")
    except ValueError:
        print("Entrada no válida. Se utilizará la gravedad de la Tierra por defecto.")
        g = 9.81
        v_escape = 11190

# Solicitar datos adicionales al usuario
v0 = float(input("Ingrese la velocidad inicial del proyectil (m/s): "))  # m/s
angle = float(input("Ingrese el ángulo de lanzamiento (grados): "))  # grados

# Convertir ángulo a radianes
angle_rad = radians(angle)

# Componentes de la velocidad inicial
vx = v0 * cos(angle_rad)
vy = v0 * sin(angle_rad)

# Crear el plano cartesiano
grid_size = 50  # Tamaño de la cuadrícula

# Crear el plano y ejes
plane = box(pos=vector(grid_size/2, -0.01, 0), size=vector(grid_size, 0.02, grid_size), color=color.gray(0.5), opacity=0.3)
x_axis = curve(pos=[vector(0, 0, 0), vector(grid_size, 0, 0)], color=color.red, radius=0.02)  # Eje X
y_axis = curve(pos=[vector(0, 0, 0), vector(0, grid_size, 0)], color=color.green, radius=0.02)  # Eje Y

# Crear marcas en los ejes
def create_ticks(axis, start, end, interval, tick_length=0.3, tick_color=color.black):
    for i in range(start, end + interval, interval):
        # Marca en el eje X
        if axis == 'x':
            tick = box(pos=vector(i, -tick_length, 0), size=vector(0.02, tick_length * 2, 0), color=tick_color)
            label(pos=vector(i, -tick_length * 2, 0), text=str(i), box=False, height=12, color=color.black)
        # Marca en el eje Y
        elif axis == 'y':
            tick = box(pos=vector(-tick_length, i, 0), size=vector(tick_length * 2, 0.02, 0), color=tick_color)
            label(pos=vector(-tick_length * 2, i, 0), text=str(i), box=False, height=12, color=color.black)

# Añadir marcas en los ejes X e Y
create_ticks('x', 0, grid_size, 5)
create_ticks('y', 0, grid_size, 5)

# Crear el proyectil
projectile = sphere(pos=vector(0, 0, 0), radius=0.5, color=color.blue, make_trail=True)

# Crear el misil en la punta del proyectil
missile = cylinder(pos=projectile.pos + vector(0, 0.5, 0), axis=vector(0, 1, 0), radius=0.2, length=1, color=color.red)

# Mostrar información en pantalla
info = label(pos=vector(25, 25, 0), text='', xoffset=1, yoffset=1, space=20, height=15, color=color.white, box=False)

# Tiempo
dt = 0.01

# Bucle infinito para repetir el movimiento
while True:
    t = 0  # Reiniciar el tiempo para cada ciclo
    
    # Reiniciar la posición y el rastro del proyectil
    projectile.pos = vector(0, 0, 0)
    missile.pos = projectile.pos + vector(0, 0.5, 0)  # Posicionar el misil en la punta
    missile.axis = vector(0, 1, 0)  # Orientación del misil
    projectile.clear_trail()  # Limpiar el rastro anterior
    
    # Simulación del movimiento parabólico
    while projectile.pos.y >= 0:
        rate(100)  # Controla la velocidad de la simulación
        x = vx * t  # Posición en x
        y = vy * t - 0.5 * g * t**2  # Posición en y con el efecto de la gravedad
        projectile.pos = vector(x, y, 0)  # Actualiza la posición del proyectil
        missile.pos = projectile.pos + vector(0, 0.5, 0)  # Actualiza la posición del misil en la punta del proyectil
        
        # Mostrar información en pantalla
        info.text = f"Tiempo: {t:.2f} s\nAltura: {projectile.pos.y:.2f} m\nDistancia: {projectile.pos.x:.2f} m"
        
        t += dt  # Incrementa el tiempo

