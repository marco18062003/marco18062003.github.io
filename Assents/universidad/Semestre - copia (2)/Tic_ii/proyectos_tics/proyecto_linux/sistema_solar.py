# modulos ____________________________
from vpython import sphere, color, vector, rate, curve
import numpy as np

# intervalo de tiempo para cada paso de la simulación
dt = 0.01  

# Crear el objeto central sol
central_sphere = sphere(pos=vector(0, 0, 0), radius=95,) 

# Crear esferas planetas
radius1 = 3  # Radio en cm (pequeño)
radius2 = 5  # Radio en cm (mediano)
radius3 = 10  # Radio de las nuevas esferas

# Función para calcular primera ley de kepler
def elliptical_orbit(a, e, theta):
    r = (a * (1 - e**2)) / (1 + e * np.cos(theta))  # Ley de áreas 
    x = r * np.cos(theta)  # Mantener el foco en el origen
    y = r * np.sin(theta)
    return vector(x, y, 0), r

# Parámetros de las órbitas elípticas
a1, e1 = 150, 0.2  # Órbita de la esfera mediana
a2, e2 = 250, 0.1  # Órbita de la esfera pequeña
a3, e3 = 350, 0.05  # Órbita para la nueva esfera 1
a4, e4 = 450, 0.3  # Órbita para la nueva esfera 2
a5, e5 = 550, 0.4  # Órbita para la nueva esfera 3

# Velocidades angulares iniciales (relacionadas con el semieje mayor de cada órbita)
speed1 = np.sqrt(1 / a1**3)  # Relación período-distancia (Tercera Ley de Kepler)
speed2 = np.sqrt(1 / a2**3)
speed3 = np.sqrt(1 / a3**3)
speed4 = np.sqrt(1 / a4**3)
speed5 = np.sqrt(1 / a5**3)

# Crear las esferas que orbitan alrededor del Sol
medium_sphere = sphere(pos=elliptical_orbit(a1, e1, 0)[0], radius=radius2, color=color.blue)
small_sphere = sphere(pos=elliptical_orbit(a2, e2, 0)[0], radius=radius1, color=color.red)

# Nuevas esferas
sphere1 = sphere(pos=elliptical_orbit(a3, e3, 0)[0], radius=radius3, color=color.green)
sphere2 = sphere(pos=elliptical_orbit(a4, e4, 0)[0], radius=radius3, color=color.orange)
sphere3 = sphere(pos=elliptical_orbit(a5, e5, 0)[0], radius=radius3, color=color.purple)

# Crear trayectorias
medium_trajectory = curve(color=color.blue)
small_trajectory = curve(color=color.red)
trajectory1 = curve(color=color.green)
trajectory2 = curve(color=color.orange)
trajectory3 = curve(color=color.purple)

# Ángulos iniciales de las órbitas
theta1, theta2, theta3, theta4, theta5 = 0, 0, 0, 0, 0

# Simulación sin detenerse
while True:
    rate(60)  # Aumenta el rate para hacer que la simulación sea más rápida y observable
    
    # Actualizar las posiciones de las esferas con velocidades angulares diferentes y respetando la excentricidad
    medium_sphere.pos, r1 = elliptical_orbit(a1, e1, theta1)
    small_sphere.pos, r2 = elliptical_orbit(a2, e2, theta2)
    sphere1.pos, r3 = elliptical_orbit(a3, e3, theta3)
    sphere2.pos, r4 = elliptical_orbit(a4, e4, theta4)
    sphere3.pos, r5 = elliptical_orbit(a5, e5, theta5)
    
    # Actualizar las trayectorias
    medium_trajectory.append(pos=medium_sphere.pos)
    small_trajectory.append(pos=small_sphere.pos)
    trajectory1.append(pos=sphere1.pos)
    trajectory2.append(pos=sphere2.pos)
    trajectory3.append(pos=sphere3.pos)

    # Incrementar ángulo con un factor aún mayor para hacer que las órbitas sean más rápidas
    theta1 += speed1 * dt * 10333300 # Aumenta el factor de velocidad aún más (400)
    theta2 += speed2 * dt * 200330
    theta3 += speed3 * dt * 303300
    theta4 += speed4 * dt * 5003330
    theta5 += speed5 * dt * 40033033
