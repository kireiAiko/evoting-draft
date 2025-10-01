import cv2

img_path = '2022-02900.jpg'
img = cv2.imread(img_path)
if img is None:
    print("Image not found. Make sure the file exists.")
    exit()

scale_percent = 45
width = int(img.shape[1] * scale_percent / 100)
height = int(img.shape[0] * scale_percent / 100)
resized = cv2.resize(img, (width, height))

BOX_SIZE = 35  # width and height of box
points = []

def click_event(event, x, y, flags, param):
    if event == cv2.EVENT_LBUTTONDOWN:
        # Convert back to original scale
        x_orig = int(x * 100 / scale_percent)
        y_orig = int(y * 100 / scale_percent)

        # Center-based box
        x_box = x_orig - BOX_SIZE // 2
        y_box = y_orig - BOX_SIZE // 2
        print(f"BBOX = ({x_box}, {y_box}, {BOX_SIZE}, {BOX_SIZE})")

        # Draw feedback rectangle
        x1 = int(x - BOX_SIZE * scale_percent / 200)
        y1 = int(y - BOX_SIZE * scale_percent / 200)
        x2 = int(x + BOX_SIZE * scale_percent / 200)
        y2 = int(y + BOX_SIZE * scale_percent / 200)
        cv2.rectangle(resized, (x1, y1), (x2, y2), (0, 0, 255), 2)
        cv2.imshow("Ballot", resized)

cv2.imshow("Ballot", resized)
cv2.setMouseCallback("Ballot", click_event)
cv2.waitKey(0)
cv2.destroyAllWindows()
